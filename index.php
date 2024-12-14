<?php
// Database configuration
$host = 'localhost';
$dbname = 'event_booking';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// USSD request handling
$sessionId = $_POST['sessionId'] ?? '';
$serviceCode = $_POST['serviceCode'] ?? '';
$phoneNumber = $_POST['phoneNumber'] ?? '';
$text = $_POST['text'] ?? '';

// Split user input
$textArray = explode("*", $text);
$level = count($textArray);

if ($text == "") {
    // Initial menu
    $response = "CON Welcome to Event Ticket Booking\n";
    $response .= "1. View Events\n";
    $response .= "2. My Bookings";
} elseif ($textArray[0] == "1") {
    // View Events
    if ($level == 1) {
        $stmt = $pdo->query("SELECT id, name, location, date, available_seats, seat_price FROM events WHERE available_seats > 0");
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($events)) {
            $response = "END No events available.";
        } else {
            $response = "CON Select an event:\n";
            foreach ($events as $event) {
                $response .= $event['id'] . ". " . $event['name'] . " (" . $event['location'] . " - " . $event['date'] . ")\nSeats: " . $event['available_seats'] . ", Price/Seat: " . $event['seat_price'] . "\n";
            }
            $response .= "0. Back to Main Menu";
        }
    } elseif ($textArray[1] == "0") {
        $response = "CON Welcome to Event Ticket Booking\n";
        $response .= "1. View Events\n";
        $response .= "2. My Bookings";
    } elseif ($level == 2) {
        $eventId = $textArray[1];

        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND available_seats > 0");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            $response = "CON Enter the number of seats for \"" . $event['name'] . "\":";
        } else {
            $response = "END Invalid event selection.";
        }
    } elseif ($level == 3) {
        $eventId = $textArray[1];
        $numSeats = $textArray[2];

        $stmt = $pdo->prepare("SELECT seat_price, available_seats FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($event) {
            // Check if the requested number of seats exceeds available seats
            if ($numSeats > $event['available_seats']) {
                $response = "END The Seat was Sold Out.";
            } else {
                $totalAmount = $event['seat_price'] * $numSeats;
                $response = "CON Confirm your booking:\n";
                $response .= "Event ID: $eventId\n";
                $response .= "Number of Seats: $numSeats\n";
                $response .= "Total Amount: $totalAmount\n";
                $response .= "1. Confirm\n";
                $response .= "2. Cancel";
            }
        } else {
            $response = "END Invalid event.";
        }
    } elseif ($level == 4) {
        $eventId = $textArray[1];
        $numSeats = $textArray[2];
        $confirmation = $textArray[3];

        if ($confirmation == "1") {
            // Check if user exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number = ?");
            $stmt->execute([$phoneNumber]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // Register user if not exists
                $stmt = $pdo->prepare("INSERT INTO users (phone_number, name) VALUES (?, ?)");
                $stmt->execute([$phoneNumber, "Guest"]);
                $userId = $pdo->lastInsertId();
            } else {
                $userId = $user['id'];
            }

            // Get seat price
            $stmt = $pdo->prepare("SELECT seat_price FROM events WHERE id = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalAmount = $event['seat_price'] * $numSeats;

            // Create booking
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, event_id, num_seat, amount, status, created_at, payment_status) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
            $stmt->execute([$userId, $eventId, $numSeats, $totalAmount, 'Confirmed', 'Paid']);

            // Update available seats
            $stmt = $pdo->prepare("UPDATE events SET available_seats = available_seats - ? WHERE id = ?");
            $stmt->execute([$numSeats, $eventId]);

            $response = "END Booking confirmed! Amount of $totalAmount has been recorded.";
        } elseif ($confirmation == "2") {
            $response = "END Booking canceled.";
        } else {
            $response = "END Invalid option.";
        }
    } else {
        $response = "END Invalid option.";
    }
} elseif ($textArray[0] == "2") {
    // My Bookings
    if ($level == 1) {
        $stmt = $pdo->prepare("SELECT b.id, e.name, e.location, e.date FROM bookings b JOIN events e ON b.event_id = e.id JOIN users u ON b.user_id = u.id WHERE u.phone_number = ?");
        $stmt->execute([$phoneNumber]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($bookings)) {
            $response = "CON You have no bookings.\n1. Back\n0. Back to Main Menu";
        } else {
            $response = "CON Your Bookings:\n";
            foreach ($bookings as $booking) {
                $response .= "- " . $booking['name'] . " (" . $booking['location'] . " - " . $booking['date'] . ")\n";
            }
            $response .= "1. Back\n0. Back to Main Menu";
        }
    } elseif ($textArray[1] == "1") {
        $response = "CON Welcome to Event Ticket Booking\n";
        $response .= "1. View Events\n";
        $response .= "2. My Bookings";
    } elseif ($textArray[1] == "0") {
        $response = "CON Welcome to Event Ticket Booking\n";
        $response .= "1. View Events\n";
        $response .= "2. My Bookings";
    } else {
        $response = "END Invalid option.";
    }
} else {
    $response = "END Invalid option.";
}

header('Content-type: text/plain');
echo $response;
?>

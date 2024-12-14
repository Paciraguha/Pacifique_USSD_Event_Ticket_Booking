<?php
$host = 'localhost';
$db = 'event_booking';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}


/*
<?php
// ussd_event_booking.php
include 'db_connection.php';

// Simulated USSD input steps
$input = [1, 1, 2]; // Example: Select Event ID, Ticket Quantity, Confirm Booking
$currentStep = 0;

if ($currentStep == 0) {
    echo "Welcome to Event Ticket Booking!\n";
    echo "Available Events:\n";

    // Fetch events from the database
    $stmt = $pdo->query("SELECT * FROM Events WHERE available_tickets > 0");
    $events = $stmt->fetchAll();

    foreach ($events as $event) {
        echo $event['event_id'] . ". " . $event['name'] . " on " . $event['date'] . " at " . $event['venue'] . " (Price: $" . $event['ticket_price'] . ")\n";
    }
    $currentStep++;
}

// Select Event
if ($currentStep == 1) {
    $selectedEventId = $input[0];
    $stmt = $pdo->prepare("SELECT * FROM Events WHERE event_id = ?");
    $stmt->execute([$selectedEventId]);
    $selectedEvent = $stmt->fetch();

    if ($selectedEvent) {
        echo "You selected: " . $selectedEvent['name'] . "\n";
        echo "How many tickets do you want?\n";
        $currentStep++;
    } else {
        echo "Invalid event selected. Try again.\n";
        exit;
    }
}

// Enter Ticket Quantity
if ($currentStep == 2) {
    $ticketQuantity = $input[1];

    if ($ticketQuantity <= $selectedEvent['available_tickets']) {
        $totalPrice = $ticketQuantity * $selectedEvent['ticket_price'];
        echo "You selected $ticketQuantity tickets for " . $selectedEvent['name'] . ". Total Price: $" . $totalPrice . "\n";
        echo "Press 1 to Confirm or 0 to Cancel.\n";
        $currentStep++;
    } else {
        echo "Not enough tickets available. Try again.\n";
        exit;
    }
}

// Confirm Booking
if ($currentStep == 3) {
    if ($input[2] == 1) {
        // Simulated user ID
        $userId = 1; // Replace with logic to identify the logged-in user

        // Update database
        $pdo->beginTransaction();
        try {
            // Insert booking record
            $stmt = $pdo->prepare("INSERT INTO Bookings (user_id, event_id, ticket_quantity, total_price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $selectedEvent['event_id'], $ticketQuantity, $totalPrice]);

            // Update available tickets
            $stmt = $pdo->prepare("UPDATE Events SET available_tickets = available_tickets - ? WHERE event_id = ?");
            $stmt->execute([$ticketQuantity, $selectedEvent['event_id']]);

            $pdo->commit();

            echo "Booking Confirmed!\n";
            echo "Reference Code: REF" . uniqid() . "\n";
            echo "Thank you for using Event Ticket Booking.\n";
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "Booking failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Booking Cancelled.\n";
    }
}
*/


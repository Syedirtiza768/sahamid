<?php
include_once("head.php");
session_start();
?>
<style>
  /* Notification Popup - Facebook Style */
  #notification-popup {
    display: none;
    position: fixed;
    bottom: 50px;
    right: 20px;
    background-color: #f9f9f9;
    /* Light gray background */
    color: #333;
    /* Darker text color */
    padding: 20px;
    /* More padding for better spacing */
    border-radius: 8px;
    /* Slightly rounded corners */
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
    font-size: 18px;
    /* Larger text size */
    width: 400px;
    /* Increased width */
    opacity: 0;
    transition: opacity 0.5s, transform 0.5s;
    transform: translateX(100%);
    cursor: pointer;
  }

  /* Make the popup larger when shown */
  #notification-popup.show {
    display: block;
    opacity: 1;
    transform: translateX(0);
  }

  /* Title for the notification */
  #notification-popup .notification-title {
    color: red;
    font-weight: bold;
    font-size: 20px;
    /* Even larger font size for title */
    margin-bottom: 10px;
    /* Space between title and content */
  }

  /* Content styling */
  #notification-popup .notification-content {
    display: flex;
    align-items: center;
  }

  /* Icon styling */
  #notification-popup .notification-icon {
    width: 40px;
    /* Larger icon size */
    height: 40px;
    margin-right: 15px;
    border-radius: 50%;
    background-color: #e0e0e0;
    /* Icon background */
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* Text styling */
  #notification-popup .notification-text {
    flex: 1;
    font-size: 18px;
    color: #333;
  }

  /* Blinking Effect */
  @keyframes blink {

    0%,
    100% {
      box-shadow: 0 0 15px rgba(255, 0, 0, 0.8);
    }

    50% {
      box-shadow: 0 0 30px rgba(255, 0, 0, 1);
    }
  }

  #notification-popup.blink {
    animation: blink 1s ease-in-out infinite;
  }


  #notification-dropdown {
    display: none;
    position: absolute;
    top: 39px;
    right: 0;
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 300px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    z-index: 1000;
  }

  .notification-header {
    color: #238dd1;
    font-weight: bold;
    padding: 10px;
    background-color: #f5f5f5;
    border-bottom: 1px solid #ddd;
    text-align: center;
  }

  #notification-dropdown ul {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 300px;
    overflow-y: auto;
  }

  #notification-dropdown li {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #f1f1f1;
  }

  #notification-dropdown li:last-child {
    border-bottom: none;
  }

  .notification-icon {
    font-size: 10px;
    color: #007bff;
    margin-right: 10px;
  }

  .notification-message {
    flex: 1;
    font-size: 14px;
    color: #333;
  }

  .notification-timestamp {
    font-size: 12px;
    color: #999;
    margin-left: 10px;
  }


  .no-notifications {
    text-align: center;
    padding: 10px;
    color: #777;
    display: none;
  }

  @media (max-width: 768px) {
    #notification-dropdown {
      width: 100%;
      right: 0;
      border-radius: 0;
    }
  }

  .notification-item {
    display: flex;
    align-items: center;
    padding: 10px;
    border-bottom: 1px solid #ccc;
  }

  .notification-content {
    display: flex;
    width: 100%;
    justify-content: space-between;
  }

  .notification-icon {
    width: 40px;
    height: 40px;
    margin-right: 10px;
  }

  .message-container {
    position: relative;
    /* This allows us to position the time at the bottom right */
    flex-grow: 1;
    /* Allow message to take up the remaining space */
  }

  .notification-time {
    position: absolute;
    bottom: 0;
    right: 0;
    font-size: 0.8em;
    color: #888;
  }

  #notification-icon {
    position: relative;
    display: inline-block;
  }

  #notification-badge {
    position: absolute;
    top: 4px;
    /* Adjust to move the badge higher */
    right: 2px;
    /* Adjust to move the badge to the left or right */
    background-color: red;
    /* Badge background color */
    color: white;
    /* Badge text color */
    border-radius: 50%;
    /* Make the badge circular */
    padding: 3px 7px;
    /* Adjust padding for badge size */
    font-size: 12px;
    /* Adjust font size */
    font-weight: bold;
    display: none;
    /* Hidden by default, shown dynamically */
  }

  #notification-badge.blink {
    animation: blink 1.5s infinite;
    /* Optional blinking animation */
  }

  /* Optional: Add a blinking animation */
  @keyframes blink {

    0%,
    100% {
      opacity: 1;
    }

    50% {
      opacity: 0;
    }
  }
</style>

<header class="main-header">
  <div id="notification-popup" onclick="closeNotification()">
    <div class="notification-title">New Notification</div>
    <div class="notification-content">
      <div class="notification-icon">
        <i class="glyphicon glyphicon-bell"></i>
      </div>
      <div class="notification-text">
        <span id="popup-message"></span>
        <div id="latest-notification"></div>
      </div>
    </div>
  </div>


  <a class="logo">
    <span class="logo-mini"><b>SA</b>H</span>
    <span class="logo-lg"><b>SA</b> Hamid</span>
  </a>
  <nav class="navbar navbar-static-top">
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </a>
    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <!-- Login As Form (Admin Only) -->
        <?php if ($_SESSION['UserID'] == "admin" || (isset($_SESSION['orignalUserID']) && $_SESSION['orignalUserID'] == "admin")) { ?>
          <li class="user" style="padding: 10px; border-right: 1px solid #ccc; display: none;">
            <form action="/sahamid/loginAs.php" method="GET">
              <input
                type="text"
                name="UserID"
                style="border:1px solid #424242; border-radius: 7px; padding: 3px"
                placeholder="Boom" />
              <button type="submit" class="btn btn-primary">Submit</button>
            </form>
          </li>
        <?php } ?>

        <!-- User Information -->
        <li class="dropdown user user-menu" style=" border-right: 0.5px solid #eeeeee;">
          <a href="#" class="dropdown-toggle" data-toggle="user-menu">
            <img src="assets/dist/img/default.jpg" class="user-image">
            <span class="hidden-xs"><?php echo $_SESSION['UsersRealName']; ?></span>
          </a>
        </li>

        <!-- Date and Time -->
        <li class="user" style="padding: 15px;">
          <span><?php echo date('d/m/Y h:i A'); ?></span>
        </li>

        <?php

        if (isset($_SESSION['UserID'])) {
          include_once("config1.php");

          // Fetch user information
          $userId = $_SESSION['UserID'];
          $query = "SELECT * FROM www_users WHERE userid = '$userId'";
          $result = mysqli_query($conn, $query);

          if (!$result) {
            die("Query failed: " . mysqli_error($conn));
          }

          if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $fullaccess = $user['fullaccess'];
          } else {
            echo "User not found.";
          }
        }
        ?>

        <!-- Notifications -->
        <?php if ($fullaccess == "12" || $fullaccess == "22" || $fullaccess == "10") { ?>
          <li class="dropdown notifications-menu" style="list-style: none; position: relative;">
            <a href="#" id="notification-icon" class="dropdown-toggle" style="position: relative;">
              <i class="glyphicon glyphicon-bell" style="font-size:18px;"></i>
              <span id="notification-badge" class="badge badge-warning blink">0</span>
            </a>
            <div id="notification-dropdown">
              <div class="notification-header">Notifications</div>
              <ul></ul>
              <div class="no-notifications">No new notifications</div>
            </div>
          </li>
        <?php } ?>

        <!-- Logout -->
        <li class="header">
          <a href="<?php echo $NewRootPath; ?>Logout.php" style="padding: 10px;">
            <span class="glyphicon glyphicon-log-out" style="font-size:18px; right:4px;"></span>
          </a>
        </li>
      </ul>
    </div>

  </nav>
</header>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const badge = document.getElementById('notification-badge');
    const dropdown = document.getElementById('notification-dropdown');
    const popup = document.getElementById('notification-popup');
    const popupMessage = document.getElementById('popup-message');
    const latestNotification = document.getElementById('latest-notification');
    let previousNotificationCount = 0;

    function fetchNotifications() {
      fetch('notifications/fetch_ogp_notifications.php')
        .then(response => response.json())
        .then(data => {
          // Update badge count
          badge.style.display = data.count > 0 ? 'flex' : 'none';
          badge.textContent = data.count;
          badge.classList.toggle('blink', data.count > previousNotificationCount);

          // Update dropdown
          const notificationList = dropdown.querySelector('ul');
          notificationList.innerHTML = '';

          data.notifications.forEach(notification => {
            const listItem = document.createElement('li');
            listItem.classList.add('notification-item'); // Optional class for styling

            // Create container for left side (icon) and right side (message)
            const notificationContent = document.createElement('div');
            notificationContent.classList.add('notification-content');

            // Left side: Icon (you can use Font Awesome or your own icon)
            const icon = document.createElement('img');
            icon.src = 'notifications/ogp_notifications.png'; // Replace with your icon path or use Font Awesome icon
            icon.alt = 'Notification Icon';
            icon.classList.add('notification-icon'); // Optional class for styling

            // Right side: Message
            const link = document.createElement('a');
            link.href = `ogp_request.php?id=${notification.id}`;
            link.textContent = notification.message;

            // Create a container for message and timestamp
            const messageContainer = document.createElement('div');
            messageContainer.classList.add('message-container');
            messageContainer.appendChild(link);

            // Bottom right: Time
            const timestamp = document.createElement('span');
            timestamp.classList.add('notification-time'); // Optional class for styling
            timestamp.textContent = notification.timestamp; // Replace with actual timestamp data

            // Append the timestamp to the message container
            messageContainer.appendChild(timestamp);

            // Handle click event to mark as read and redirect
            link.addEventListener('click', event => {
              event.preventDefault(); // Prevent immediate navigation
              markNotificationAsRead(notification.id, notification.request_no, link.href);
            });

            // Append elements to notification content container
            notificationContent.appendChild(icon);
            notificationContent.appendChild(messageContainer);
            listItem.appendChild(notificationContent);

            // Append the notification item to the list
            notificationList.appendChild(listItem);
          });

          // Show popup for new notifications

          if (data.count > previousNotificationCount) {
            popupMessage.textContent = `You have new notifications!`;
            latestNotification.textContent = `Latest: ${data.notifications[0]?.message || 'No new notifications'}`;

            // Add 'show' and 'blink' classes to make the popup appear and blink
            popup.classList.add('show', 'blink');

            // Stop blinking after 5 seconds
            setTimeout(() => {
              popup.classList.remove('blink');
            }, 5000);

            // Automatically hide the popup after 10 seconds
            setTimeout(() => {
              popup.classList.remove('show');
            }, 10000);
          }

          previousNotificationCount = data.count;
        })
        .catch(console.error);
    }


    // Helper function to format timestamp (you can adjust the format as needed)
    function formatTimestamp(timestamp) {
      const date = new Date(timestamp * 1000); // Assuming timestamp is in seconds
      return date.toLocaleTimeString(); // Formats the time based on the locale
    }


    function markNotificationAsRead(notificationId, requestNo, url) {
      fetch('notifications/mark_ogp_notifications_read.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            notificationId
          })
        })
        .then(response => response.json())
        .then(() => {
          // Append request_no to URL and navigate
          window.location.href = `${url}&request_no=${requestNo}`;
        })
        .catch(console.error);
    }


    document.getElementById('notification-icon').addEventListener('click', event => {
      event.preventDefault();
      dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
      badge.classList.remove('blink');
    });

    document.addEventListener('click', event => {
      if (!dropdown.contains(event.target) && !event.target.closest('#notification-icon')) {
        dropdown.style.display = 'none';
      }
    });

    fetchNotifications();
    setInterval(fetchNotifications, 10000);
  });

  function closeNotification() {
    const popup = document.getElementById('notification-popup');
    popup.classList.remove('show');
  }
</script>
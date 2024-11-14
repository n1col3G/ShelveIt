<head>
    <style>
        #friendRequestsContainer {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            padding: 10px;
        }
    </style>
</head>

<!-- Modal for Adding a Friend, Friend Requests, and Friends List -->
<div class="modal fade" id="friendModal" tabindex="-1" aria-labelledby="friendModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="friendModalLabel">ShelveIt! Friends</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" id="friendModalTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="friends-list-tab" data-bs-toggle="tab" data-bs-target="#friends-list" type="button" role="tab" aria-controls="friends-list" aria-selected="true">Friends</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-friend-tab" data-bs-toggle="tab" data-bs-target="#add-friend" type="button" role="tab" aria-controls="add-friend" aria-selected="false">Add Friends</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="friend-requests-tab" data-bs-toggle="tab" data-bs-target="#friend-requests" type="button" role="tab" aria-controls="friend-requests" aria-selected="false">Friend Requests</button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="friendModalTabContent">
                    <!-- Add Friends Tab -->
                    <div class="tab-pane fade" id="add-friend" role="tabpanel" aria-labelledby="add-friend-tab">
                        <form id="friendRequestForm" class="mt-3">
                            <div class="mb-3">
                                <label for="friendUserID" class="form-label">Enter Friend's Account Number</label>
                                <input type="text" class="form-control" id="friendUserID" placeholder="Account #">
                            </div>
                            <button type="button" class="btn btn-primary" onclick="sendFriendRequest()">Send Friend Request</button>
                        </form>
                    </div>

                    <!-- Friend Requests Tab -->
                    <div class="tab-pane fade" id="friend-requests" role="tabpanel" aria-labelledby="friend-requests-tab">
                        <h5 class="mt-3">Friend Requests</h5>
                        <div id="friendRequestsContainer">
                            <!-- Friend requests will be loaded here -->
                        </div>
                    </div>

                    <!-- Friends List Tab -->
                    <div class="tab-pane fade show active" id="friends-list" role="tabpanel" aria-labelledby="friends-list-tab">
                        <h5 class="mt-3">Friends</h5>
                        <div id="friendButtons">
                            <!-- Friends list will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Function to open the modal
    function openFriendModal() {
        const friendModal = new bootstrap.Modal(document.getElementById('friendModal'));
        console.log("openFriendModal called");
        friendModal.show();
        // Load data for the Friend Requests and Friends tabs
        loadFriendsRequests();
        loadFriends();
    }

    function sendFriendRequest() {
        const recipientID = document.getElementById('friendUserID').value;
        if (!recipientID) {
            alert("Please enter an Account Number to send a friend request.");
            return;
        }
        
        fetch('addFriend.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({recipientID})
        })
        .then(response => response.text())  // Use .text() instead of .json() to inspect the raw response
        .then(text => {
            console.log("Raw response:", text);  // Log raw response for debugging
            const data = JSON.parse(text);      // Now parse it as JSON if it's valid
            alert(data.status);
        })
        .catch(error => console.error("Error sending friend request:", error));
    }

    function loadFriendsRequests() {
        console.log("loadFriendsRequests called");
        fetch('loadFriendReq.php')
            .then(response => response.json())
            .then(data => {
                //console.log("Response data:", data);
                const requestContainer = document.getElementById("friendRequestsContainer");
                requestContainer.innerHTML = ''; // Clear existing content

                if (data.length === 0) {
                    requestContainer.innerHTML = "No friend requests found.";
                    return;
                }

                data.forEach(request => {
                    const requestDiv = document.createElement("div");
                    requestDiv.classList.add("friendRequest");
                    requestDiv.textContent = `Friend request from ${request.Firstname} ${request.Lastname} on ${request.requestDate}`;

                    if (request.status === 'Pending') {
                        const acceptButton = document.createElement("button");
                        acceptButton.textContent = "Accept";
                        acceptButton.onclick = () => handleFriendRequest(request.requestID, 'Accepted');
                        
                        const rejectButton = document.createElement("button");
                        rejectButton.textContent = "Reject";
                        rejectButton.onclick = () => handleFriendRequest(request.requestID, 'Rejected');

                        requestDiv.appendChild(acceptButton);
                        requestDiv.appendChild(rejectButton);
                    } else {
                        requestDiv.textContent += ` - ${request.status}`;
                    }

                    requestContainer.appendChild(requestDiv);
                });
            })
            .catch(error => console.error("Error loading friend requests:", error));
            //console.log("Response data:", data);
    }

    function handleFriendRequest(requestID, status) {
        fetch('friendRequests.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({requestID, status})
        })
        .then(response => response.json())
        .then(data => {
            alert(data.status);
            loadFriendsRequests();
            loadFriends();
        })
        .catch(error => console.error("Error handling friend request:", error));
    }

    function loadFriends() {
        fetch('loadFriends.php')
        .then(response => response.json())
        .then(friends => {
            console.log('Friends data:', friends); // Check if this logs an array

            if (!Array.isArray(friends)) {
                console.error('Error: Expected an array, but received:', friends);
                return;
            }
            const friendButtonsContainer = document.getElementById('friendButtons');
            friendButtonsContainer.innerHTML = ''; // Clear existing content

            friends.forEach(friend => {
                const friendDiv = document.createElement("div");
                friendDiv.textContent = `${friend.Firstname} ${friend.Lastname} (Friends since ${friend.friendshipDate})`;

                const viewLibraryButton = document.createElement("button");
                viewLibraryButton.classList.add('btn', 'btn-info');
                viewLibraryButton.textContent = "View Library";
                //viewLibraryButton.onclick = () => viewLibrary(friend.userID);
                //console.log(friend);
                //console.log(friend.UserID);
                const friendUserID = friend.UserID;
                viewLibraryButton.onclick = () => {
                    //const friendUserID = friend.UserID;
                    if (friendUserID) {
                        window.location.href = `viewLibrary.php?friendUserID=${friendUserID}`;
                    } else {
                        console.error("Friend's userID is undefined");
                    }
                };
                friendDiv.appendChild(viewLibraryButton);
                friendButtonsContainer.appendChild(friendDiv);

                //const viewLibraryButton = document.createElement('button');
                //viewLibraryButton.classList.add('btn', 'btn-info');
               // viewLibraryButton.textContent = 'View Library';
                
                //friendButtonsContainer.appendChild(viewLibraryButton);
            });
        })
        .catch(error => console.error('Error loading friends:', error));
    }

    function viewLibrary(friendUserID) {
        window.location.href = `viewLibrary.php?friendUserID=${friendUserID}`;
    }
</script>


<!-- Include Bootstrap JS at the end of the body (if not already included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
import Peer from "simple-peer";

let channel = null;
let currentRoomId = null;
let isInCall = false;

function playRingSound() {
    const audioContext = new (window.AudioContext ||
        window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    oscillator.frequency.setValueAtTime(800, audioContext.currentTime); // Frequency in Hz
    oscillator.type = "sine"; // Waveform type

    gainNode.gain.setValueAtTime(0.1, audioContext.currentTime); // Volume

    oscillator.start();
    window.currentRingOscillator = oscillator;
    window.currentRingContext = audioContext;
}

function stopRingSound() {
    if (window.currentRingOscillator) {
        window.currentRingOscillator.stop();
        window.currentRingOscillator = null;
        window.currentRingContext = null;
    }
}

function startVideoCall(roomId, isInitiator) {
    currentRoomId = roomId;
    isInCall = true;
    // Join presence channel sekali saja
    channel = Echo.join(`video-call.${roomId}`);

    // Dapatkan media
    navigator.mediaDevices
        .getUserMedia({ video: true, audio: true })
        .then((stream) => {
            const localVideo = document.getElementById("local-video");
            localVideo.srcObject = stream;

            const peer = new Peer({
                initiator: isInitiator,
                trickle: false,
                stream: stream,
            });

            // Jika simple-peer menghasilkan sinyal, kirim ke lawan
            peer.on("signal", (data) => {
                channel.whisper("signal", {
                    signal: data,
                    from: window.userId,
                });
            });

            // Jika menerima stream dari peer lain
            peer.on("stream", (remoteStream) => {
                const remoteVideo = document.getElementById("remote-video");
                remoteVideo.srcObject = remoteStream;
            });

            // Terima whisper dari user lain
            channel.listenForWhisper("signal", (e) => {
                // Pastikan bukan sinyal dari diri sendiri
                if (e.from !== window.userId) {
                    peer.signal(e.signal);
                }
            });
        });
}

// Load users
async function loadUsers() {
    const response = await fetch("/api/users");
    const users = await response.json();
    const userList = document.getElementById("user-list");
    userList.innerHTML = "";
    users.forEach((user) => {
        const button = document.createElement("button");
        button.className =
            "px-4 py-2 bg-blue-500 text-gray-700 rounded mr-2 mb-2";
        button.textContent = `Call ${user.name}`;
        button.addEventListener("click", () => inviteUser(user.id, user.name));
        userList.appendChild(button);
    });
}

// Invite user
function inviteUser(userId, userName) {
    if (isInCall) {
        alert('You are already in a call. End the current call first.');
        return;
    }

    if (!window.onlineUsers || !window.onlineUsers.includes(parseInt(userId))) {
        // Send missed call message
        fetch("/chat/send", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                message: `Missed video call from ${window.userName}`,
            }),
        });
        alert(`${userName} is offline. Sent a missed call notification.`);
        return;
    }

    const roomId = `room-${window.userId}-${userId}-${Date.now()}`;
    // Broadcast invite
    fetch("/broadcast/video-call-invite", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({
            roomId: roomId,
            toUserId: userId,
            fromUserName: window.userName,
        }),
    });
    // Start call as initiator
    startVideoCall(roomId, true);
    document.getElementById("video-call-container").classList.remove("hidden");
    document.getElementById("end-call").classList.remove("hidden");
}

window.inviteUser = inviteUser;

// Listen for invites
Echo.private(`user.${window.userId}`).listen(
    ".App\\Events\\VideoCallInvite",
    (e) => {
        // Play ring sound (beep)
        playRingSound();

        document.getElementById(
            "invite-message"
        ).textContent = `${e.fromUserName} is calling you.`;
        document
            .getElementById("invite-notification")
            .classList.remove("hidden");
        currentRoomId = e.roomId;
    }
);

// Load users on load
document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById("user-list")) {
        loadUsers();
    }

    // Event listeners for invite
    if (document.getElementById("accept-invite")) {
        document
            .getElementById("accept-invite")
            .addEventListener("click", () => {
                stopRingSound();
                startVideoCall(currentRoomId, false);
                document
                    .getElementById("video-call-container")
                    .classList.remove("hidden");
                document
                    .getElementById("invite-notification")
                    .classList.add("hidden");
                document.getElementById("end-call").classList.remove("hidden");
            });
    }

    if (document.getElementById("decline-invite")) {
        document
            .getElementById("decline-invite")
            .addEventListener("click", () => {
                // Broadcast decline
                fetch("/broadcast/video-call-decline", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                    },
                    body: JSON.stringify({
                        roomId: currentRoomId,
                        toUserId: window.userId, // the one declining
                        fromUserName: window.userName,
                    }),
                });
                stopRingSound();
                document
                    .getElementById("invite-notification")
                    .classList.add("hidden");
            });
    }

    // Event listeners for video-call page
    if (document.getElementById("start-call")) {
        document.getElementById("start-call").addEventListener("click", () => {
            const roomId = "room-" + Date.now();
            startVideoCall(roomId, true);
            document
                .getElementById("video-call-container")
                .classList.remove("hidden");
            document.getElementById("start-call").classList.add("hidden");
            document.getElementById("end-call").classList.remove("hidden");
        });
    }

    if (document.getElementById("end-call")) {
        document.getElementById("end-call").addEventListener("click", () => {
            // Broadcast end
            fetch("/broadcast/video-call-end", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({
                    roomId: currentRoomId,
                    fromUserName: window.userName,
                }),
            });
            isInCall = false;
            // Stop camera
            const localVideo = document.getElementById("local-video");
            if (localVideo && localVideo.srcObject) {
                const stream = localVideo.srcObject;
                stream.getTracks().forEach(track => track.stop());
                localVideo.srcObject = null;
            }
            const remoteVideo = document.getElementById("remote-video");
            if (remoteVideo && remoteVideo.srcObject) {
                const stream = remoteVideo.srcObject;
                stream.getTracks().forEach(track => track.stop());
                remoteVideo.srcObject = null;
            }
            document
                .getElementById("video-call-container")
                .classList.add("hidden");
            if (document.getElementById("start-call")) {
                document
                    .getElementById("start-call")
                    .classList.remove("hidden");
            }
            document.getElementById("end-call").classList.add("hidden");
            if (channel) {
                channel.leave();
            }
        });
    }

    if (document.getElementById("join-call")) {
        document.getElementById("join-call").addEventListener("click", () => {
            const roomId = document.getElementById("room-id-input").value;
            if (roomId) {
                startVideoCall(roomId, false);
                document
                    .getElementById("video-call-container")
                    .classList.remove("hidden");
                document.getElementById("join-call").classList.add("hidden");
                document.getElementById("end-call").classList.remove("hidden");
            }
        });
    }

    // handle ketika dia di tolak panggilan
    Echo.private(`user.${window.userId}`).listen(
        ".App\\Events\\VideoCallDeclined",
        (e) => {
            alert(`${e.fromUserName} has declined your call.`);
            isInCall = false;
            document
                .getElementById("video-call-container")
                .classList.add("hidden");
            if (document.getElementById("start-call")) {
                document
                    .getElementById("start-call")
                    .classList.remove("hidden");
            }
            document.getElementById("end-call").classList.add("hidden");
            if (channel) {
                channel.leave();
            }
        }
    );

    // handle ketika dia sudah memanggil tidak bisa memanggil lagi
    Echo.private(`user.${window.userId}`).listen(
        ".App\\Events\\VideoCallBusy",
        (e) => {
            alert(`${e.fromUserName} is currently busy on another call.`);
        }
    );

    // handle ketika dia nge end call maka yang lain juga ke end
    Echo.private('user.' + window.userId).listen('.App\\Events\\VideoCallEnded', (e) => {
        alert(`The call has been ended by ${e.fromUserName}.`);
        isInCall = false;
        // Stop camera
        const localVideo = document.getElementById("local-video");
        if (localVideo && localVideo.srcObject) {
            const stream = localVideo.srcObject;
            stream.getTracks().forEach(track => track.stop());
            localVideo.srcObject = null;
        }
        const remoteVideo = document.getElementById("remote-video");
        if( remoteVideo && remoteVideo.srcObject) {
            const stream = remoteVideo.srcObject;
            stream.getTracks().forEach(track => track.stop());
            remoteVideo.srcObject = null;
        }
        document.getElementById("video-call-container").classList.add("hidden");
        if (document.getElementById("start-call")) {
            document.getElementById("start-call").classList.remove("hidden");
        }
        document.getElementById("end-call").classList.add("hidden");
        if (channel) {
            channel.leave();
        }
    });

});

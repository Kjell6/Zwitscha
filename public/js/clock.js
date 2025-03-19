function updateClock() {
  const now = new Date();
  const paragraph = document.getElementById("uhrzeit");
  paragraph.innerHTML = now.toLocaleTimeString();
}

const speed = 1000; // refresh interval in milliseconds
const interval = setInterval(updateClock, 1000);

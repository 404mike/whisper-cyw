document.addEventListener("DOMContentLoaded", function() {
    const audioPlayer = document.getElementById('audioPlayer');
    if (!audioPlayer) {
        console.error("Audio element with ID 'audioPlayer' not found.");
        return;
    }
    createChapter(audioPlayer);
});

const createChapter = (audioPlayer) => {
    fetch('chapters.json')
        .then(response => response.json())
        .then(data => {
            console.log(data);
            const chapters = data;
            const chapterList = document.getElementById('chapters');
            chapters.forEach(chapter => {
                const li = document.createElement('li');
                li.textContent = chapter.title;
                // When a chapter is clicked, set the audio player's current time and play the audio.
                li.addEventListener('click', () => {
                    audioPlayer.currentTime = timeStringToSeconds(chapter["start time"]);
                    audioPlayer.play();
                });
                chapterList.appendChild(li);
            });
        })
        .catch(error => console.error('Error loading chapters:', error));
}

// Helper function to convert "HH:MM:SS.mmm" to seconds.
function timeStringToSeconds(timeString) {
    const parts = timeString.split(':');
    const hours = parseInt(parts[0], 10);
    const minutes = parseInt(parts[1], 10);
    const seconds = parseFloat(parts[2]);
    return hours * 3600 + minutes * 60 + seconds;
}

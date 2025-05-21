document.addEventListener("DOMContentLoaded", function () {
    const audio = document.getElementById("audioPlayer");
    if (!audio) return;

    // Get all transcript containers
    const transcriptContainers = document.querySelectorAll(".transcript_container");

    // Create an array to hold container/lines pairs
    const transcriptGroups = Array.from(transcriptContainers).map(container => {
        return {
            container: container,
            lines: container.querySelectorAll(".transcript_line")
        };
    });

    function parseTimeToSeconds(timeString) {
        const parts = timeString.split(":");
        const seconds = parseFloat(parts[2]); // Includes milliseconds
        return parseInt(parts[0]) * 3600 + parseInt(parts[1]) * 60 + seconds;
    }

    function findActiveLine(lines, currentTime) {
        let activeLine = null;

        lines.forEach((line) => {
            const lineId = line.getAttribute("data-time");
            const lineTime = parseTimeToSeconds(lineId);

            if (currentTime >= lineTime) {
                activeLine = line;
            }
        });

        return activeLine;
    }

    audio.addEventListener("timeupdate", function () {
        const currentTime = audio.currentTime;

        transcriptGroups.forEach(({ container, lines }) => {
            const activeLine = findActiveLine(lines, currentTime);

            if (activeLine) {
                // Remove active class from all lines in this container
                lines.forEach(line => line.classList.remove("active"));

                // Add active class to the matched line
                activeLine.classList.add("active");

                // Scroll to the active line
                container.scrollTo({
                    top: activeLine.offsetTop - container.offsetTop,
                    behavior: "smooth"
                });
            }
        });
    });
});

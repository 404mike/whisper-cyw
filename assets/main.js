document.addEventListener("DOMContentLoaded", function () {
    const audio = document.getElementById("audioPlayer");
    if(!audio) return;

    const transcriptContainer = document.getElementById("transcript_container");
    const transcriptLines = document.querySelectorAll(".transcript_line");

    function parseTimeToSeconds(timeString) {
        const parts = timeString.split(":");
        const seconds = parseFloat(parts[2]); // Includes milliseconds
        return parseInt(parts[0]) * 3600 + parseInt(parts[1]) * 60 + seconds;
    }

    function findActiveLine(currentTime) {
        let activeLine = null;

        transcriptLines.forEach((line) => {
            const lineId = line.id;
            const lineTime = parseTimeToSeconds(lineId);

            if (currentTime >= lineTime) {
                activeLine = line;
            }
        });

        return activeLine;
    }

    audio.addEventListener("timeupdate", function () {
        const currentTime = audio.currentTime;
        const activeLine = findActiveLine(currentTime);

        if (activeLine) {
            // Remove active class from all lines
            transcriptLines.forEach((line) => line.classList.remove("active"));

            // Add active class to current line
            activeLine.classList.add("active");

            // Scroll transcript container so the active line is at the top
            transcriptContainer.scrollTo({
                top: activeLine.offsetTop - transcriptContainer.offsetTop,
                behavior: "smooth",
            });
        }
    });
});


function filterSelection(lang, language) {
    let count = 0;

    if (lang === "all") {
        const articles = document.querySelectorAll("[data-lang]");
        articles.forEach((article) => {
            article.style.display = "block";
            count++;
        });

        document.getElementById("article_count").innerText = count + ' Audio Transcripts';
        return;
    }

    const articles = document.querySelectorAll("[data-lang]");
    articles.forEach((article) => {
        let articleLang = article.getAttribute("data-lang");
        if (articleLang === lang) {
            article.style.display = "block";
            count++;
        } else {
            article.style.display = "none";
        }
    });
    document.getElementById("article_count").innerText = count + ' Audio Transcripts - (' + language + ')';
};
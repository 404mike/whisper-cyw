let entities = [];
let currentEntity = null;

// load entities from JSON file
fetch("../entities/entities-images.json")
    .then((response) => response.json())
    .then((data) => {
        data.forEach((entity) => {
            entities.push(entity);
        });
    })
    .catch((error) => {
        console.error("Error loading entities:", error);
    });

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
            getActiveLine();

            // Scroll transcript container so the active line is at the top
            transcriptContainer.scrollTo({
                top: activeLine.offsetTop - transcriptContainer.offsetTop,
                behavior: "smooth",
            });
        }
    });
});


function getActiveLine() {
    let elements = document.getElementsByClassName('transcript_line');
    let position = -1; // Default value if no element with class 'active' is found

    Array.from(elements).forEach((element, index) => {
        if (element.classList.contains('active')) {
            position = index;
        }
    });

    if (position === -1) {
        console.log("No active line found.");
        return;
    }

    if(position !== currentEntity) {
        currentEntity = position;
        showEntity(entities[position]);
    }

    // console.log(position);
}
function showEntity(entities) {
    let entitiesContainer = document.getElementById("entities");

    entitiesContainer.innerHTML = "";

    for (const [key, value] of Object.entries(entities)) {
        // console.log(`${key}: ${value}`);
        // console.log(value);
        let qid = key;
        let title = value.name;
        let image = value.image;

        if (!image) {
            imagesrc = "";
        }
        else{
            imagesrc = `<img src="${image}" alt="${title}">`;
        }

        // Create a new div element with a title and image that link to the QID
        let div = document.createElement("div");
        div.className = "entity";
        div.innerHTML = `
            
            <a href="https://www.wikidata.org/wiki/${qid}" target="_blank">
               <h3>${title}</h3>
            ${imagesrc}
            </a>
        `;
        // Add the div to the container
        entitiesContainer.appendChild(div);

        console.log('Showing entity:', qid, title, image);
    }
}
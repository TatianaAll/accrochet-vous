// create infobubble
let divContainer = document.querySelector("div.isFlex.flexBetween.w90");
let aForInfoBubble = document.querySelector("a.disabled");

//add an addEventListener when the mouse is over the link
aForInfoBubble.addEventListener("mouseover",  () => {
    if (!aForInfoBubble.querySelector('.infobubble')) {
        const infobubble = document.createElement('div');
        infobubble.className = "infobubble";
        infobubble.innerText = "Il faut être rédacteur pour soumettre un article";
        aForInfoBubble.appendChild(infobubble);
        }
    });

aForInfoBubble.addEventListener("mouseout", () => {
    // delete the info bubble when the mouse is out the link
    const infobubble = aForInfoBubble.querySelector('.infobubble');
    if (infobubble) {
        aForInfoBubble.removeChild(infobubble);
    }
});
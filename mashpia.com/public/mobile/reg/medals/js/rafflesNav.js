const red = '#ed224b';
const yellow = '#ffd624';
const darkBlue = '#232c7b';
const grey = '#b3b3b0';

const raffleNavData = {
    raffle5: {
        daysLeft: 1,
        raffleName: "this weeek's 5 Flag Raffle"
    },
    raffle60: {
        daysLeft: 0,
        raffleName: 'the 60 Flag Raffle #2'
    },
    raffle180: {
        daysLeft: 50,
        raffleName: 'the 180 Flag Raffle'
    }
};

const createElementWithClass = (elm, className) => {
    let element = document.createElement(elm)
    element.classList.add(className);
    return element;
}


const loadRaffleContainer = (flag, data) => {
    const id = new URLSearchParams(window.location.search).get('id');

    let flagSrc, stroke;

    if (flag === 5) {
        flagSrc = data.daysLeft === 0 ? '/mobile/reg/images/5-flag.png' : '/mobile/reg/images/5-flag-empty.png';
        stroke = red;
    }
    if (flag === 60) {
        flagSrc = data.daysLeft === 0 ? '/mobile/reg/images/60-flag.png' : '/mobile/reg/images/60-flag-empty.png';
        stroke = yellow;
    }
    if (flag === 180) {
        flagSrc = data.daysLeft === 0 ? '/mobile/reg/images/180-flag.png' : '/mobile/reg/images/180-flag-empty.png';
        stroke = darkBlue;
    }

    let container = createElementWithClass('div', 'raffleInfo');
    container.onclick = function () { window.location.href = `/mobile/reg/raffle${flag}.html?id=${id}` };

    let raffleId = createElementWithClass('div', 'raffleID');
    raffleId.classList.add('item');

    let raffleFlagContainer = createElementWithClass('div', 'raffleFlagContainer');
    let img = document.createElement('img');
    img.setAttribute('src', flagSrc);
    img.setAttribute('alt', 'flag');
    raffleFlagContainer.appendChild(img);


    const xmlns = "http://www.w3.org/2000/svg";
    let perimeter = 2 * 3.14 * 43; //r
    let percent = (flag - data.daysLeft) / flag * 100;
    let fillAmount = perimeter - perimeter * percent / 100;
    const progress = document.createElementNS(xmlns, 'svg');
    progress.setAttribute('height', 100);
    progress.setAttribute('width', 100);
    const circle = document.createElementNS(xmlns, 'circle');
    circle.setAttribute('r', 43);
    circle.setAttribute('cy', 50.625);
    circle.setAttribute('cx', 50.625);
    circle.setAttribute('fill', 'transparent');
    circle.setAttribute('stroke-width', 10);
    circle.setAttribute('stroke', stroke);
    circle.setAttribute('data-fill', percent);
    circle.setAttribute('stroke-dasharray', perimeter);
    circle.setAttribute('stroke-dashoffset', fillAmount);
    circle.setAttribute('class', 'circle-animation');
    circle.setAttribute('ic', 'circle');
    progress.appendChild(circle);

    const backgroundCircle = document.createElementNS(xmlns, 'svg');
    backgroundCircle.setAttribute('height', 100);
    backgroundCircle.setAttribute('width', 100);
    backgroundCircle.setAttribute('class', 'background_circle');
    const circle2 = document.createElementNS(xmlns, 'circle');
    circle2.setAttribute('r', 43);
    circle2.setAttribute('cy', 50.625);
    circle2.setAttribute('cx', 50.625);
    circle2.setAttribute('fill', 'transparent');
    circle2.setAttribute('stroke-width', 10);
    circle2.setAttribute('stroke', grey);
    circle2.setAttribute('class', 'circle-animation');
    circle2.setAttribute('ic', 'circle');
    backgroundCircle.appendChild(circle2);

    let raffleDetails = createElementWithClass('div', 'raffleDetails');
    let daysLeft = createElementWithClass('p', 'daysLeft');
    daysLeft.style.color = stroke;
    daysLeft.innerText = data.daysLeft === 0 ? 'MAZAL TOV!' : `${data.daysLeft} MORE DAYS`;
    let daysLeft2 = document.createElement('p');
    daysLeft2.innerText = data.daysLeft === 0 ? `you have been entered into ${data.raffleName}` : `of missions to enter ${data.raffleName}`;
    let details = createElementWithClass('p', 'details');
    details.innerText = 'details >>';
    raffleDetails.appendChild(daysLeft);
    raffleDetails.appendChild(daysLeft2);
    raffleDetails.appendChild(details);

    raffleId.appendChild(raffleFlagContainer);
    raffleId.appendChild(progress);
    raffleId.appendChild(backgroundCircle);
    container.appendChild(raffleId);
    container.appendChild(raffleDetails);

    return container;
}

document.addEventListener("DOMContentLoaded", function (event) {

    const raffleInfoContainer = document.getElementById('raffleInfoContainer');

    let raffle5 = loadRaffleContainer(5, raffleNavData.raffle5);
    let raffle60 = loadRaffleContainer(60, raffleNavData.raffle60);
    let raffle180 = loadRaffleContainer(180, raffleNavData.raffle180);

    raffleInfoContainer.appendChild(raffle5);
    raffleInfoContainer.appendChild(raffle60);
    raffleInfoContainer.appendChild(raffle180);
})

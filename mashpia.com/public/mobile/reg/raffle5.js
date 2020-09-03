const red = '#ed224b';
const yellow = '#ffd624';
const darkBlue = '#232c7b';
const grey = '#b3b3b0';
const xmlns = "http://www.w3.org/2000/svg";

const trackRecordData = [
    //needs to be sorted from newest to oldest
    {
        parsha: 'ויצא',
        won: false,
        prize: 'Cap',
        days: [{ completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }]
    }, {
        parsha: 'תולדות',
        won: false,
        prize: 'T Shirt',
        days: [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
    }, {
        parsha: 'חיי שרה',
        won: false,
        prize: 'Sweatshirt',
        days: [{ completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
    }, {
        parsha: 'וירא',
        won: false,
        prize: 'Mug',
        days: [{ completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
    }, {
        parsha: 'לך לך',
        won: true,
        prize: 'Fidget Spinner',
        days: [{ completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
    }, {
        parsha: 'נח',
        won: false,
        prize: 'Mug',
        days: [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
    }, {
        parsha: 'בראשית',
        won: false,
        prize: 'Sweatshirt',
        days: [{ completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
    }
];

const currentWeekData = {
    img: '/mobile/reg/images/tzivos_hashem_mug.png',
    name: 'Tzivos Hashem Mug'
}

const createElementWithClass = (elm, className) => {
    let element = document.createElement(elm)
    element.classList.add(className);
    return element;
}

const createBigDonutChart = () => {
    let raffleId = document.getElementById('raffleID');

    let prizeDetails = createElementWithClass('div', 'prizeDetails');

    let raffleFlagContainer = createElementWithClass('div', 'raffleFlagContainer');
    let img = document.createElement('img');
    img.setAttribute('src', '/mobile/reg/images/5-flag.png');
    img.setAttribute('alt', 'flag');
    raffleFlagContainer.appendChild(img);

    let p = document.createElement('p');
    p.innerText = "This week's prize:"

    let prizeTitle = document.createElement('p');
    let b = document.createElement('b');
    b.innerText = currentWeekData.name;
    prizeTitle.appendChild(b);
    let prizeImg = document.createElement('img');
    prizeImg.setAttribute('src', currentWeekData.img);
    prizeImg.setAttribute('alt', currentWeekData.name);

    prizeDetails.appendChild(raffleFlagContainer);
    prizeDetails.appendChild(p);
    prizeDetails.appendChild(prizeTitle);
    prizeDetails.appendChild(prizeImg);

    const svg = document.createElementNS(xmlns, 'svg');
    svg.setAttribute('height', 340);
    svg.setAttribute('width', 340);
    const circle = document.createElementNS(xmlns, 'circle');
    circle.setAttribute('r', 146.2);
    circle.setAttribute('cy', 172.125);
    circle.setAttribute('cx', 172.125);
    circle.setAttribute('fill', 'transparent');
    circle.setAttribute('stroke-width', 18);
    circle.setAttribute('stroke', red);
    circle.setAttribute('class', 'circle-animation');
    circle.setAttribute('ic', 'circle');
    svg.appendChild(circle);

    raffleId.appendChild(prizeDetails);
    raffleId.appendChild(svg);
}


const createSmallDonutChart = (donutData) => {
    const div = document.createElement('div');
    let amountCompleted = donutData.days.filter(d => d.completed).length;
    div.classList.add('raffleInfo');
    const svg = document.createElementNS(xmlns, 'svg');
    svg.setAttribute('height', 100);
    svg.setAttribute('width', 100);
    let startingVal = 100;
    let perimeter = 2 * 3.14 * 43; //r


    if (!donutData.won) {
        donutData.days.forEach(day => {
            let stroke = day.completed ? '#ed224b' : day.past ? grey : '#ed224b1a';
            let amount = startingVal;
            let fillAmount = perimeter - perimeter * amount / 100;
            let circle = document.createElementNS(xmlns, 'circle');
            circle.setAttribute('r', 43);
            circle.setAttribute('cy', 50.625);
            circle.setAttribute('cx', 50.625);
            circle.setAttribute('fill', 'transparent');
            circle.setAttribute('stroke-width', 10);
            circle.setAttribute('stroke', stroke);
            circle.setAttribute('data-fill', startingVal);
            circle.setAttribute('stroke-dasharray', perimeter);
            circle.setAttribute('stroke-dashoffset', fillAmount);
            svg.appendChild(circle);

            startingVal -= (100 / 7);
            if (amountCompleted < 7) {
                fillAmount = perimeter - perimeter * (startingVal + 1) / 100;
                let circle = document.createElementNS(xmlns, 'circle');
                circle.setAttribute('r', 43);
                circle.setAttribute('cy', 50.625);
                circle.setAttribute('cx', 50.625);
                circle.setAttribute('fill', 'transparent');
                circle.setAttribute('stroke-width', 10);
                circle.setAttribute('stroke', '#fff');
                circle.setAttribute('data-fill', startingVal);
                circle.setAttribute('stroke-dasharray', perimeter - 1);
                circle.setAttribute('stroke-dashoffset', fillAmount);
                svg.appendChild(circle);
            }
        });
    }


    let raffleDetails = document.createElement('div');
    raffleDetails.classList.add('raffleDetails');
    let parsha = document.createElement('p');
    parsha.classList.add('parsha');
    parsha.innerText = donutData.parsha;
    raffleDetails.appendChild(parsha);

    div.appendChild(raffleDetails);

    let raffleID = document.createElement('div');
    raffleID.classList.add('item', 'raffleID');
    let smallRaffleFlagContainer = document.createElement('div');
    smallRaffleFlagContainer.classList.add('smallRaffleFlagContainer');
    let img = document.createElement('img');
    img.src = amountCompleted > 4 ? '/mobile/reg/images/5-flag-plain.png' : '/mobile/reg/images/5-flag-plain-empty.png';
    img.alt = 'flag';
    let h2 = document.createElement('h2');
    h2.innerText = amountCompleted;

    smallRaffleFlagContainer.appendChild(img);
    smallRaffleFlagContainer.appendChild(h2);

    if (donutData.won) {
        let textSVG = document.createElementNS(xmlns, 'svg');
        textSVG.setAttribute('viewBox', '0 0 101 101');
        textSVG.setAttribute('id', 'circleText')
        textSVG.setAttribute('width', '100');
        textSVG.setAttribute('height', '100');
        let path = document.createElementNS(xmlns, 'path');
        path.setAttribute('d', 'M7.625,50.625a43,43 0 1,0 86,0a43,43 0 1,0 -86,0');
        path.setAttribute('id', 'curve');
        path.setAttribute('transform', 'translate(100), scale(-0.98, 0.98)')
        let text = document.createElementNS(xmlns, 'text');
        text.setAttribute('width', '500');
        text.innerHTML = `<textPath xlink:href="#curve">${'Congratulations! You won the ' + donutData.prize + '!'}</textPath>`
        textSVG.appendChild(path);
        textSVG.appendChild(text);
        raffleID.appendChild(textSVG);

        let innerCircle = document.createElementNS(xmlns, 'circle');
        innerCircle.setAttribute('r', 43);
        innerCircle.setAttribute('cy', 50.625);
        innerCircle.setAttribute('cx', 50.625);
        innerCircle.setAttribute('fill', '#ffd624');
        svg.appendChild(innerCircle);

        let fillAmount = perimeter - perimeter * startingVal / 100;
        let circle = document.createElementNS(xmlns, 'circle');
        circle.setAttribute('r', 43);
        circle.setAttribute('cy', 50.625);
        circle.setAttribute('cx', 50.625);
        circle.setAttribute('fill', 'transparent');
        circle.setAttribute('stroke-width', 10);
        circle.setAttribute('stroke', '#ed224b');
        circle.setAttribute('data-fill', startingVal);
        circle.setAttribute('stroke-dasharray', perimeter);
        circle.setAttribute('stroke-dashoffset', fillAmount);
        svg.appendChild(circle);
    }

    raffleID.appendChild(smallRaffleFlagContainer);
    raffleID.appendChild(svg);

    div.appendChild(raffleID);
    return div;
};

const loadDonutCharts = () => {
    const trackRecordContainer = document.getElementById("trackRecordContainer");
    let index = 0;
    let div;
    trackRecordData.forEach(trackRecord => {
        if (index % 3 === 0) {
            if (div) trackRecordContainer.appendChild(div);
            div = document.createElement('div');
        }
        let smallDonutChart = createSmallDonutChart(trackRecord);
        div.appendChild(smallDonutChart);
        index += 1
    });
    trackRecordContainer.appendChild(div);
};

const clearPastWinners = () => {
    document.getElementById('previousWinnerParsha').innerText = '';
    document.getElementById('nextWinnerParsha').innerText = '';
    document.getElementById('parsha-header').innerText = '';
    document.getElementById('parshaPrizeContainer').innerHTML = '';
    document.getElementById('parshaFlagContainer').innerHTML = '';
    document.getElementById('boys').innerHTML = '<h2>Boys</h2>';
    document.getElementById('girls').innerHTML = '<h2>Girls</h2>';
    document.getElementById('parshaContainer').innerHTML += '<div class="loader" id="loader"></div>';
}

const loadPastWinners = () => {
    document.getElementById('loader').remove();
    if (pastWinnersData.previous) {
        let previousWinnerParsha = document.getElementById('previousWinnerParsha');
        previousWinnerParsha.onclick = () => fetchPastWinners(pastWinnersData.previous.id);
        previousWinnerParsha.innerText = `<< ${pastWinnersData.previous.name}`
    }
    if (pastWinnersData.next) {
        let nextWinnerParsha = document.getElementById('nextWinnerParsha');
        nextWinnerParsha.onclick = () => fetchPastWinners(pastWinnersData.next.id);
        nextWinnerParsha.innerText = `${pastWinnersData.next.name} >>`
    }

    document.getElementById('parsha-header').innerText = `פרשת ${pastWinnersData.raffle.name}`;

    let parshaPrizeContainer = document.getElementById('parshaPrizeContainer');
    let img = document.createElement('img');
    let prize = pastWinnersData.raffle.winner_info.boys[0] ? pastWinnersData.raffle.winner_info.boys[0] : pastWinnersData.raffle.winner_info.girls[0];
    let w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    let pic_src = w > 850 ? prize.prize_picture.full : prize.prize_picture.thumb;
    img.setAttribute('src', `//mashpia.com/${encodeURI(pic_src)}`);
    img.setAttribute('alt', prize.prize_name);
    let prizeName = document.createElement('p');
    prizeName.innerText = prize.prize_name;
    parshaPrizeContainer.appendChild(img);
    parshaPrizeContainer.appendChild(prizeName);

    let parshaFlagContainer = document.getElementById('parshaFlagContainer');
    let flagImg = document.createElement('img');
    flagImg.setAttribute('src', pastWinnersData.raffle.days_completed > 4 ? '/mobile/reg/images/5-flag-plain.png' : '/mobile/reg/images/5-flag-plain-empty.png');
    flagImg.setAttribute('alt', 'flag');
    let h2 = document.createElement('h2');
    h2.innerText = pastWinnersData.raffle.days_completed;
    parshaFlagContainer.appendChild(flagImg);
    parshaFlagContainer.appendChild(h2);

    // Load boys column
    if (pastWinnersData.raffle.winner_info && pastWinnersData.raffle.winner_info.boys.length) {
        let boys = document.getElementById('boys');
        loadColumn(pastWinnersData.raffle.winner_info.boys, boys);
    }

    // Load girls column
    if (pastWinnersData.raffle.winner_info && pastWinnersData.raffle.winner_info.girls.length) {
        let girls = document.getElementById('girls');
        loadColumn(pastWinnersData.raffle.winner_info.girls, girls);
    }

};

const loadColumn = (arr, elm) => {
    arr.forEach(item => {
        let container = document.createElement('div');
        let name = document.createElement('p');
        name.innerText = `${item.rank || ''} ${item.name || ''}`;
        let school = document.createElement('p');
        school.innerText = item.school;
        let grade = document.createElement('p');
        grade.innerText = `Grade: ${item.grade}`;
        container.appendChild(name);
        container.appendChild(school);
        container.appendChild(grade);
        elm.appendChild(container);
    })
}

let pastWinnersData = {
    next: {},
    previous: {},
    raffle: {}
}

let pastWinnersDataLoading = true;

const fetchPastWinners = async (id) => {
    clearPastWinners();
    pastWinnersDataLoading = true;
    try {
        let data = await axios.get(`/mobile/news/api/raffle.php?raffle_id=${id}`);
        if (data.data && data.data.success) {
            pastWinnersData = data.data.data;
            pastWinnersData.raffle.days_completed = 7; //TODO: get from backend
            pastWinnersData.raffle.prize = {
                img: '/mobile/reg/images/tzivos_hashem_mug.png',
                name: 'Tzivos Hashem Mug'
            }
            pastWinnersDataLoading = false;
            loadPastWinners();
        }
    } catch (err) {
        pastWinnersDataLoading = false;
        console.log(err)
    }
};

const init = () => {
    createBigDonutChart();
    loadDonutCharts();
    fetchPastWinners(0);
}
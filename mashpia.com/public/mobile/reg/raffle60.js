const red = '#ed224b';
const yellow = '#ffd624';
const darkBlue = '#232c7b';
const grey = '#b3b3b0';
const xmlns = "http://www.w3.org/2000/svg";

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
};

const loadPastWinners = () => {

    Object.keys(sixtyFlagWinnerData).forEach(raffleNumer => {
        const { prize = {}, year = '', raffles = {} } = sixtyFlagWinnerData[raffleNumer];
        const winnersContainer = createElementWithClass('div', 'winnersContainer');
        const prizeHeader = createElementWithClass('h2', 'prizeHeader');
        prizeHeader.innerText = `${raffleNumer}. ${prize.name || ''}`;
        const img = document.createElement('img');
        if (prize.img && prize.name) {
            loadImage(img, prize.img, prize.thumb, prize.name)
        }
        const yearBanner = createElementWithClass('p', 'year');
        yearBanner.innerText = `${year} winners:`;

        const winners = createElementWithClass('div', 'winners');
        const boys = createElementWithClass('div', 'column');
        const girls = createElementWithClass('div', 'column');

        Object.keys(raffles).forEach(raffle => {
            const raffleNameBoys = createElementWithClass('p', 'raffleName');
            raffleNameBoys.innerText = `Raffle ${raffle} - Boys`;
            const raffleNameGirls = createElementWithClass('p', 'raffleName');
            raffleNameGirls.innerText = `Raffle ${raffle} - Girls`;
            boys.appendChild(raffleNameBoys);
            girls.appendChild(raffleNameGirls);
            let boyWinners = raffles[raffle]['boys'] || [];
            let girlWinners = raffles[raffle]['girls'] || [];
            loadColumn(boyWinners, boys);
            loadColumn(girlWinners, girls);
        });


        winners.appendChild(boys);
        winners.appendChild(girls);

        winnersContainer.appendChild(prizeHeader);
        winnersContainer.appendChild(img);
        winnersContainer.appendChild(yearBanner);
        winnersContainer.appendChild(winners);
        const innerContainer = document.getElementById('innerWhoWon');
        innerContainer.appendChild(winnersContainer);
    });
};

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
    img.setAttribute('src', '/mobile/reg/images/60-flag.png');
    img.setAttribute('alt', 'flag');
    raffleFlagContainer.appendChild(img);

    let p = document.createElement('p');
    p.innerText = "Win incredible prizes like these!"

    let prizeImg = document.createElement('img');
    prizeImg.setAttribute('src', '/mobile/reg/images/60-flag-prizes.png');
    prizeImg.setAttribute('alt', '60 Flag prizes');

    prizeDetails.appendChild(raffleFlagContainer);
    prizeDetails.appendChild(p);
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
    circle.setAttribute('stroke', yellow);
    circle.setAttribute('class', 'circle-animation');
    circle.setAttribute('ic', 'circle');
    svg.appendChild(circle);

    raffleId.appendChild(prizeDetails);
    raffleId.appendChild(svg);
};

const load60FlagDonutCharts = () => {
    const raffle60Container = document.getElementById('raffle60Container');
    sixtyFlagRaffleData.forEach(raffle => {
        const { year, endMonth, startMonth, daysTillDrawing, raffleNumber, months, daysCompleted } = raffle;
        const raffleHeader = createElementWithClass('div', 'raffleHeader');
        const div = document.createElement('div');
        const parshaContainer = createElementWithClass('div', 'parshaContainer');
        const parsha = createElementWithClass('p', 'parsha');
        parsha.innerText = `${startMonth} - ${endMonth} ${year}`;
        parshaContainer.appendChild(parsha);

        if (daysTillDrawing > 0) {
            const drawing = createElementWithClass('p', 'drawing');
            drawing.innerText = `${daysTillDrawing} days to the drawing!`;
            parshaContainer.appendChild(drawing);
        }

        let calendar = createElementWithClass('img', 'calendar');
        calendar.setAttribute('src', `/mobile/reg/images/calendar${raffleNumber}.png`);

        raffleHeader.appendChild(div);
        raffleHeader.appendChild(parshaContainer);
        raffleHeader.appendChild(calendar);

        const svg = document.createElementNS(xmlns, 'svg');
        svg.setAttribute('height', 340);
        svg.setAttribute('width', 340);

        const totalAmountOfDays = months[Object.keys(months)[0]].length + months[Object.keys(months)[1]].length + months[Object.keys(months)[2]].length;
        const perimeter = 2 * 3.14 * 146.2; //r
        const innerPerimeter = 2 * 3.14 * 110; //r
        let percentFill = 100;
        let innerPercentFill = 100;
        let rotation = 160;

        let svgContainer = createElementWithClass('div', 'svgContainer');

        Object.keys(months).forEach((month, monthIndex) => {
            let monthPercentOfDonut = months[month].length / totalAmountOfDays * 100;
            let monthStroke = `#fff9e7`;
            let monthAmount = innerPercentFill;
            let fill = innerPerimeter - innerPerimeter * monthAmount / 100;
            let id = 'curve' + month;

            let circle = document.createElementNS(xmlns, 'circle');
            circle.setAttribute('r', 110);
            circle.setAttribute('cy', 170);
            circle.setAttribute('cx', 170);
            circle.setAttribute('fill', 'transparent');
            circle.setAttribute('stroke-width', 35);
            circle.setAttribute('stroke', monthStroke);
            circle.setAttribute('data-fill', innerPercentFill);
            circle.setAttribute('stroke-dasharray', innerPerimeter);
            circle.setAttribute('stroke-dashoffset', fill);
            svg.appendChild(circle);

            let textSVG = document.createElementNS(xmlns, 'svg');
            textSVG.setAttribute('viewBox', '10 0 340 340');
            textSVG.setAttribute('width', '110');
            textSVG.setAttribute('height', '110');
            textSVG.setAttribute('class', 'textSVG');
            let path = document.createElementNS(xmlns, 'path');
            path.setAttribute('d', 'M60,170a110,110 0 1,0 220,0a110,110 0 1,0 -220,0');
            path.setAttribute('id', id);
            path.setAttribute('fill', 'transparent')
            let text = document.createElementNS(xmlns, 'text');
            text.setAttribute('width', '500');
            text.innerHTML = `<textPath xlink:href="#${id}">${month}</textPath>`
            textSVG.appendChild(path);
            textSVG.appendChild(text);
            textSVG.setAttribute('style', `transform: rotate(${rotation}deg)`)

            svgContainer.appendChild(textSVG);

            innerPercentFill -= monthPercentOfDonut;
            rotation += (360 / Object.keys(months).length);

            fill = innerPerimeter - innerPerimeter * (innerPercentFill + 1) / 100;
            // let circle2 = document.createElementNS(xmlns, 'circle');
            // circle2.setAttribute('r', 110);
            // circle2.setAttribute('cy', 170);
            // circle2.setAttribute('cx', 170);
            // circle2.setAttribute('fill', 'transparent');
            // circle2.setAttribute('stroke-width', 35);
            // circle2.setAttribute('stroke', '#fff');
            // circle2.setAttribute('data-fill', innerPercentFill);
            // circle2.setAttribute('stroke-dasharray', innerPerimeter - 1);
            // circle2.setAttribute('stroke-dashoffset', fill);
            // svg.appendChild(circle2);

            months[month].forEach((day, dayIndex) => {
                let stroke = day.completed ? yellow : day.past ? grey : `#fff9e7`;
                let amount = percentFill;
                let fillAmount = perimeter - perimeter * amount / 100;
                let circle = document.createElementNS(xmlns, 'circle');
                circle.setAttribute('r', 146.2);
                circle.setAttribute('cy', 170);
                circle.setAttribute('cx', 170);
                circle.setAttribute('fill', 'transparent');
                circle.setAttribute('stroke-width', 35);
                circle.setAttribute('stroke', stroke);
                circle.setAttribute('data-fill', percentFill);
                circle.setAttribute('stroke-dasharray', perimeter);
                circle.setAttribute('stroke-dashoffset', fillAmount);
                svg.appendChild(circle);

                percentFill -= (100 / (totalAmountOfDays + 1));

                // let lastMonth = Object.keys(months)[Object.keys(months).length - 1]
                // if (month === lastMonth && index === months[lastMonth].length - 1) {
                //     fillAmount = perimeter - perimeter * (percentFill + 1) / 100;
                //     let circle2 = document.createElementNS(xmlns, 'circle');
                //     circle2.setAttribute('r', 146.2);
                //     circle2.setAttribute('cy', 170);
                //     circle2.setAttribute('cx', 170);
                //     circle2.setAttribute('fill', 'transparent');
                //     circle2.setAttribute('stroke-width', 35);
                //     circle2.setAttribute('stroke', '#fff');
                //     circle2.setAttribute('data-fill', percentFill);
                //     circle2.setAttribute('stroke-dasharray', perimeter - 1); //916
                //     circle2.setAttribute('stroke-dashoffset', fillAmount + 8); //917
                //     svg.appendChild(circle2);
                //     return
                // };
                if ((monthIndex === Object.keys(months).length - 1) && dayIndex === months[month].length - 1) return;
                fillAmount = perimeter - perimeter * (percentFill + 1) / 100;
                let circle2 = document.createElementNS(xmlns, 'circle');
                circle2.setAttribute('r', 146.2);
                circle2.setAttribute('cy', 170);
                circle2.setAttribute('cx', 170);
                circle2.setAttribute('fill', 'transparent');
                circle2.setAttribute('stroke-width', 35);
                circle2.setAttribute('stroke', '#fff');
                circle2.setAttribute('data-fill', percentFill);
                circle2.setAttribute('stroke-dasharray', perimeter - 8);
                circle2.setAttribute('stroke-dashoffset', fillAmount);
                svg.appendChild(circle2);


            });

        });

        let flagContainer = createElementWithClass('div', 'flagContainer');
        let flag = createElementWithClass('img', 'flag');
        flag.setAttribute('src', `/mobile/reg/images/60-flag-plain${daysCompleted < 60 ? '-empty' : ''}.png`);
        let numberOfDays = createElementWithClass('p', 'numberOfDays');
        numberOfDays.innerText = daysCompleted;
        flagContainer.appendChild(flag);
        flagContainer.appendChild(numberOfDays);


        svgContainer.appendChild(svg);
        svgContainer.appendChild(flagContainer);


        raffle60Container.appendChild(raffleHeader);
        raffle60Container.appendChild(svgContainer);
    });
};

const loadImage = (elm, img, thumb, name) => {
    let w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    let pic_src = ((w > 850) || !thumb) ? img : thumb;
    elm.setAttribute('src', `//mashpia.com/${encodeURI(pic_src)}`);
    elm.setAttribute('alt', name);
}

let sixtyFlagRaffleData = [
    //sorted from newest to oldest - even days inside month. newest days should come first in the array
    {
        raffleNumber: 4,
        startMonth: 'כסלו',
        endMonth: 'שבט',
        year: 'תשפ״א',
        daysTillDrawing: 16,
        daysCompleted: 67,
        months: {
            'Kislev': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Teves': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Shvat': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }]
        }
    }, {
        raffleNumber: 3,
        startMonth: 'כסלו',
        endMonth: 'שבט',
        year: 'תשפ״א',
        daysTillDrawing: 0,
        daysCompleted: 50,
        months: {
            'Kislev': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Teves': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Shvat': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }]
        }
    }, {
        raffleNumber: 2,
        startMonth: 'כסלו',
        endMonth: 'שבט',
        year: 'תשפ״א',
        daysTillDrawing: 0,
        daysCompleted: 80,
        months: {
            'Kislev': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Teves': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Shvat': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }]
        }
    }, {
        raffleNumber: 1,
        startMonth: 'כסלו',
        endMonth: 'שבט',
        year: 'תשפ״א',
        daysTillDrawing: 0,
        daysCompleted: 35,
        months: {
            'Kislev': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Teves': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Shvat': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }]
        }
    }
];

const fetchRaffleData = async (id) => {
    try {
        let response = await axios.get('/mobile/api/raffles/monthly.php?action=raffle-data&user_id=' + id);
        if (response.data) {
            sixtyFlagRaffleData = response.data[0];
            load60FlagDonutCharts();
        }
    } catch (err) {
        pastWinnersDataLoading = false;
        console.log(err)
    }
};

let sixtyFlagWinnerData = {
    4: {
        prize: {
            name: 'Tzivos Hashem Mug',
            img: '/mobile/reg/images/tzivos_hashem_mug.png'
        },
        year: '5780',
        raffles: {
            1: {
                boys: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }],
                girls: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }]
            },
            2: {
                boys: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }],
                girls: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }]
            }
        }
    },
    3: {
        prize: {
            name: 'Tzivos Hashem Mug',
            img: '/mobile/reg/images/tzivos_hashem_mug.png'
        },
        year: '5780',
        raffles: {
            1: {
                boys: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }],
                girls: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }]
            },
            2: {
                boys: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }],
                girls: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }]
            }
        }
    },
    2: {
        prize: {
            name: 'Tzivos Hashem Mug',
            img: '/mobile/reg/images/tzivos_hashem_mug.png'
        },
        year: '5780',
        raffles: {
            1: {
                boys: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }],
                girls: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }]
            },
            2: {
                boys: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }],
                girls: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }]
            }
        }
    },
    1: {
        prize: {
            name: 'Tzivos Hashem Mug',
            img: '/mobile/reg/images/tzivos_hashem_mug.png'
        },
        year: '5780',
        raffles: {
            1: {
                boys: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }],
                girls: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }]
            },
            2: {
                boys: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }],
                girls: [{
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }, {
                    name: 'Chaim Azimov',
                    grade: '1 - Boys',
                    rank: 'Colonel',
                    school: 'Cheder Menachem',
                }]
            }
        }
    }
};

let pastWinnersDataLoading = true;

const fetchPastWinners = async (id) => {
    pastWinnersDataLoading = true;
    try {
        let response = await axios.get('/mobile/api/raffles/monthly.php?action=winner-data&user_id=' + id);
        // let data2 = await axios.get(`/mobile/api/raffles/weekly.php?action=completed&user_id=${user_id}`);
        if (response.data) {
            sixtyFlagWinnerData = response.data[0];
            // pastWinnersData.raffle.days_completed = data2.data.days_completed; //TODO: get from backend
            pastWinnersDataLoading = false;
            loadPastWinners();
        }
    } catch (err) {
        pastWinnersDataLoading = false;
        console.log(err)
    }
};

const init = () => {
    const id = new URLSearchParams(window.location.search).get('id');
    if (localStorage.getItem("login")) {
        // document.getElementById("mainLink").setAttribute('href', '/mobile/reg/medals/?id=' + id);
        // document.getElementById("missionsLink").setAttribute('href', '/mobile/missionsNew.html?id=' + id);
        // document.getElementById("rankLink").setAttribute('href', '/mobile/reg/rank.html?id=' + id);
        document.getElementById("flagLink5").setAttribute('href', '/mobile/reg/raffle5.html?id=' + id);
        document.getElementById("flagLink60").setAttribute('href', '/mobile/reg/raffle60.html?id=' + id);
        document.getElementById("flagLink180").setAttribute('href', '/mobile/reg/raffle180.html?id=' + id);
    }

    $.post('../reg/ajax/getPhoto.php', { user_id: id }, function (success) {
        var info = $.parseJSON(success);
        var html = '<a href="/mobile/reg/medals/index.html?id=' + id + '">';
        if (info.mobile_pic) html += '<img id="userImg" src="https://mashpia.com/mobile/reg/' + info.mobile_pic + '">';
        else if (info.thumb) html += '<img id="userImg" src="https://mashpia.com/thumbs/' + info.thumb + '">';
        else if (info.photo) html += '<img id="userImg" src="https://mashpia.com/file_view.php?id=' + info.photo + '">';
        html += '</a>';
        $(".personalImg").append(html);
    });

    //TODO
    // if (lang == 2) {
    //     $(".container").eq(1).addClass('he'); // add a he class to the page
    //     $(".container.he").attr('dir', 'rtl'); // set the text direction to the other direction...
    //     $(".personalImg").css({ "right": "2%" }); // move the profile photo over a bit...
    // } else {
    //     $(".personalImg").css({ "left": "2%" }); // move the profile image a but from the edge...
    // }

    createBigDonutChart();
    fetchRaffleData(id);
    fetchPastWinners(id);
}
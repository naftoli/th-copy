const red = '#ed224b';
const yellow = '#ffd624';
const darkBlue = '#232c7b';
const grey = '#b3b3b0';
const xmlns = "http://www.w3.org/2000/svg";

const sixtyFlagRaffleData = [
    //sorted from newest to oldest
    {
        raffleNumber: 4,
        startMonth: 'כסלו',
        endMonth: 'שבט',
        year: 'תשפ״א',
        raffleDate: 'י״ט אייר',
        daysTillDrawing: 16,
        daysCompleted: 67,
        months: {
            'Iyar': [{ completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: true, past: false }, { completed: true, past: false }],
            'Nissan': [{ completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }],
            'Adar': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Shvat': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Teves': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Kislev': [{ completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Cheshvon': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Tishrei': [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }],
            'Elul': [{ completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: true }],
        }
    }
];

const createElementWithClass = (elm, className) => {
    let element = document.createElement(elm)
    element.classList.add(className);
    return element;
};

const createBigDonutChart = () => {
    let raffleId = document.getElementById('raffleID');

    let prizeDetails = createElementWithClass('div', 'prizeDetails');

    let raffleFlagContainer = createElementWithClass('div', 'raffleFlagContainer');
    let img = document.createElement('img');
    img.setAttribute('src', '/mobile/reg/images/180-flag.png');
    img.setAttribute('alt', 'flag');
    raffleFlagContainer.appendChild(img);

    let p = document.createElement('p');
    p.innerText = "Will you make it into the grand raffle?"

    let prizeImg = document.createElement('img');
    prizeImg.setAttribute('src', '/mobile/reg/images/180-flag-prizes.png');
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
    circle.setAttribute('stroke', darkBlue);
    circle.setAttribute('class', 'circle-animation');
    circle.setAttribute('ic', 'circle');
    svg.appendChild(circle);

    raffleId.appendChild(prizeDetails);
    raffleId.appendChild(svg);
};

const load60FlagDonutCharts = () => {
    const raffle60Container = document.getElementById('raffle60Container');
    sixtyFlagRaffleData.forEach(raffle => {
        const { year, endMonth, startMonth, daysTillDrawing, raffleDate, months, daysCompleted } = raffle;
        const raffleHeader = createElementWithClass('div', 'raffleHeader');
        const div = document.createElement('div');
        const parshaContainer = createElementWithClass('div', 'parshaContainer');
        const parsha = createElementWithClass('p', 'parsha');
        parsha.innerText = `${raffleDate} ${year}`;
        parshaContainer.appendChild(parsha);

        if (daysTillDrawing > 0) {
            const drawing = createElementWithClass('p', 'drawing');
            drawing.innerText = `${daysTillDrawing} days to the drawing!`;
            parshaContainer.appendChild(drawing);
        }

        let calendar = createElementWithClass('img', 'calendar');
        calendar.setAttribute('src', `/mobile/reg/images/blank-calendar.png`);

        raffleHeader.appendChild(div);
        raffleHeader.appendChild(parshaContainer);
        raffleHeader.appendChild(calendar);

        const svg = document.createElementNS(xmlns, 'svg');
        svg.setAttribute('height', 340);
        svg.setAttribute('width', 340);

        const totalAmountOfDays = Object.keys(months).reduce((cur, month) => {
            return months[month].length + cur
        }, 0);
        const perimeter = 2 * 3.14 * 146.2; //r
        const innerPerimeter = 2 * 3.14 * 110; //r
        let percentFill = 100;
        let innerPercentFill = 100;
        let rotation = 82;

        let svgContainer = createElementWithClass('div', 'svgContainer');

        Object.keys(months).forEach(month => {
            let monthPercentOfDonut = months[month].length / totalAmountOfDays * 100;
            let monthStroke = `${darkBlue}1a`;
            let monthAmount = innerPercentFill;
            let fill = innerPerimeter - innerPerimeter * monthAmount / 100;
            let id = 'curve' + month

            let innerCircle = document.createElementNS(xmlns, 'circle');
            innerCircle.setAttribute('r', 110);
            innerCircle.setAttribute('cy', 170);
            innerCircle.setAttribute('cx', 170);
            innerCircle.setAttribute('fill', 'transparent');
            innerCircle.setAttribute('stroke-width', 35);
            innerCircle.setAttribute('stroke', monthStroke);
            innerCircle.setAttribute('data-fill', innerPercentFill);
            innerCircle.setAttribute('stroke-dasharray', innerPerimeter);
            innerCircle.setAttribute('stroke-dashoffset', fill);
            svg.appendChild(innerCircle);

            let textSVG = document.createElementNS(xmlns, 'svg');
            textSVG.setAttribute('viewBox', '0 0 340 340');
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
            rotation -= (360) / Object.keys(months).length;

            let totalMonths = Object.keys(months).length;

            fill = innerPerimeter - innerPerimeter * (innerPercentFill + 1) / 100;
            let monthDivider = document.createElementNS(xmlns, 'circle');
            monthDivider.setAttribute('r', 110);
            monthDivider.setAttribute('cy', 170);
            monthDivider.setAttribute('cx', 170);
            monthDivider.setAttribute('fill', 'transparent');
            monthDivider.setAttribute('stroke-width', 35);
            monthDivider.setAttribute('stroke', '#fff');
            monthDivider.setAttribute('data-fill', innerPercentFill);
            monthDivider.setAttribute('stroke-dasharray', innerPerimeter - 4);
            monthDivider.setAttribute('stroke-dashoffset', fill);
            svg.appendChild(monthDivider);


            months[month].forEach((day, index) => {
                let stroke = day.completed ? darkBlue : day.past ? grey : `${darkBlue}1a`;
                percentFill = createDayStroke(svg, perimeter, percentFill, stroke, totalAmountOfDays, totalMonths);
                if (index === months[month].length - 1) {
                    percentFill = createLargeDivider(svg, perimeter, percentFill, totalAmountOfDays, totalMonths);
                } else {
                    divide(svg, perimeter, percentFill);
                }


            })

        });

        let flagContainer = createElementWithClass('div', 'flagContainer');
        let flag = createElementWithClass('img', 'flag');
        flag.setAttribute('src', `/mobile/reg/images/180-flag-plain${daysCompleted < 180 ? '-empty' : ''}.png`);
        let numberOfDays = createElementWithClass('p', 'numberOfDays');
        // numberOfDays.innerText = daysCompleted;
        numberOfDays.innerText = '';
        flagContainer.appendChild(flag);
        flagContainer.appendChild(numberOfDays);


        svgContainer.appendChild(svg);
        svgContainer.appendChild(flagContainer);


        raffle60Container.appendChild(raffleHeader);
        raffle60Container.appendChild(svgContainer);
    });
};

const createDayStroke = (svg, perimeter, percentFill, stroke, totalAmountOfDays, totalMonths) => {
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
    percentFill -= ((100 - (0.5 * totalMonths)) / (totalAmountOfDays));
    return percentFill;
}

const divide = (svg, perimeter, percentFill) => {
    let fillAmount = perimeter - perimeter * (percentFill) / 100;
    let circle2 = document.createElementNS(xmlns, 'circle');
    circle2.setAttribute('r', 146.2);
    circle2.setAttribute('cy', 170);
    circle2.setAttribute('cx', 170);
    circle2.setAttribute('fill', 'transparent');
    circle2.setAttribute('stroke-width', 35);
    circle2.setAttribute('stroke', '#fff');
    circle2.setAttribute('data-fill', percentFill);
    circle2.setAttribute('stroke-dasharray', perimeter - 1);
    circle2.setAttribute('stroke-dashoffset', fillAmount);
    svg.appendChild(circle2);
}

const createLargeDivider = (svg, perimeter, percentFill, totalAmountOfDays, totalMonths) => {
    percentFill += ((100 - (0.5 * totalMonths)) / (totalAmountOfDays));
    percentFill -= 0.5;
    fillAmount = perimeter - perimeter * (percentFill) / 100;
    let circle = document.createElementNS(xmlns, 'circle');
    circle.setAttribute('r', 146.2);
    circle.setAttribute('cy', 170);
    circle.setAttribute('cx', 170);
    circle.setAttribute('fill', 'transparent');
    circle.setAttribute('stroke-width', 35);
    circle.setAttribute('stroke', '#fff');
    circle.setAttribute('data-fill', percentFill);
    circle.setAttribute('stroke-dasharray', perimeter);
    circle.setAttribute('stroke-dashoffset', fillAmount);
    percentFill -= ((100 - (0.5 * totalMonths)) / (totalAmountOfDays));
    svg.appendChild(circle);
    return percentFill;
}


const init = () => {
    createBigDonutChart();
    load60FlagDonutCharts();
};
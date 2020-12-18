const red = '#e92d41';
const yellow = '#ffd942';
const darkBlue = '#1b2b51';
const grey = '#b3b3b0';
const xmlns = "http://www.w3.org/2000/svg";

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

    new Chart('raffle180Doughnut', {
        // The type of chart we want to create
        type: 'doughnut',

        // The data for our dataset
        data: {
            datasets: [{
                data: [100],
                backgroundColor: [darkBlue],
                borderWidth: 0,
            }]
        },

        // Configuration options go here
        options: {
            cutoutPercentage: 90,
            aspectRatio: 1,
            responsive: true,
            elements: {
                arc: {
                    backgroundColor: 'white',
                    borderColor: 'white',
                    borderWidth: 0,
                }
            },
            legend: {
                labels: {
                    fontColor: 'black',
                    boxWidth: 0
                },
                display: false
            },
            tooltips: {
                enabled: false
            }
        }
    });

    raffleId.appendChild(prizeDetails);
};

const load180FlagDonutCharts = () => {
    const raffle180Container = document.getElementById('raffle180Container');
    raffleData.forEach(raffle => {
        const { year, daysTillDrawing, raffleDate, months, daysCompleted, orderBy } = raffle;
        const raffleHeader = document.getElementById('raffleHeader');
        const div = document.createElement('div');
        const parshaContainer = createElementWithClass('div', 'parshaContainer');
        const parsha = createElementWithClass('p', 'parsha');
        parsha.innerText = `${new Date(raffleDate).toDateString()}`;
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

        let doughnutContainer = document.getElementById('doughnutContainer');

        let data = [];
        let backgroundColors = [];
        let totalAmountOfDaysInRaffle = 0;

        orderBy.forEach(month => {
            months[month].forEach(day => {
                totalAmountOfDaysInRaffle += 1;
                backgroundColors.push(day.completed ? darkBlue : day.past ? grey : '#e9eaf2')
            })
        })

        data = [...backgroundColors].map(() => 100 / totalAmountOfDaysInRaffle);


        new Chart('doughnutChart', {
            // The type of chart we want to create
            type: 'doughnut',

            // The data for our dataset
            data: {
                datasets: [{
                    data,
                    backgroundColor: backgroundColors,
                    borderWidth: 1,
                }, {
                    data: [100],
                    backgroundColor: ['#e9eaf2'],
                    borderWidth: 0,
                }]
            },

            // Configuration options go here
            options: {
                cutoutPercentage: 55,
                aspectRatio: 1,
                responsive: true,
                elements: {
                    arc: {
                        backgroundColor: 'white',
                        borderColor: 'white',
                        borderWidth: 0,
                    }
                },
                legend: {
                    labels: {
                        fontColor: 'black',
                        boxWidth: 0
                    },
                    display: false
                },
                tooltips: {
                    enabled: false
                }
            }
        });

        let rotation = 110;

        orderBy.forEach((month, index) => {
            let id = 'curve' + month;

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

            doughnutContainer.appendChild(textSVG);

            rotation += (360) / orderBy.length;

        });

        let flagContainer = createElementWithClass('div', 'flagContainer');
        let flag = createElementWithClass('img', 'flag');
        flag.setAttribute('src', `/mobile/reg/images/180-flag-plain${daysCompleted < 180 ? '-empty' : ''}.png`);
        let numberOfDays = createElementWithClass('p', 'numberOfDays');
        numberOfDays.innerText = daysCompleted;
        flagContainer.appendChild(flag);
        flagContainer.appendChild(numberOfDays);

        doughnutContainer.appendChild(flagContainer);

        raffle180Container.appendChild(raffleHeader);
        raffle180Container.appendChild(doughnutContainer);
    });
};

const loadImage = (elm, img, thumb, name) => {
    let w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    let pic_src = ((w > 850) || !thumb) ? img : thumb;
    elm.setAttribute('src', `//mashpia.com/${encodeURI(pic_src)}`);
    elm.setAttribute('alt', name);
}

let raffleData = [
    {
        year: 'תשפ״א',
        raffleDate: 'י״ט אייר',
        daysTillDrawing: 16,
        daysCompleted: 67,
        months: {
            //sorted from newest to oldest
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

const fetchRaffleData = async (id) => {
    try {
        let response = await axios.get('/mobile/api/raffles/yearly.php?action=raffle-data&user_id=' + id);
        if (response.data) {
            raffleData = response.data[0];
            load180FlagDonutCharts();
        }
    } catch (err) {
        pastWinnersDataLoading = false;
        console.log(err)
    }
};


const init = () => {
    const id = new URLSearchParams(window.location.search).get('id');
    if (localStorage.getItem("login")) {
        document.getElementById("flagLink5").setAttribute('href', '/mobile/reg/raffle5.html?id=' + id);
        document.getElementById("flagLink60").setAttribute('href', '/mobile/reg/raffle60.html?id=' + id);
        document.getElementById("flagLink180").setAttribute('href', '/mobile/reg/raffle180.html?id=' + id);
        let raffleNumber = window.location.pathname.split('.html')[0].split('raffle').pop();
        document.getElementById("flagLink" + raffleNumber).classList.add('activeFlagLink');
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
    fetchRaffleData(id)
};
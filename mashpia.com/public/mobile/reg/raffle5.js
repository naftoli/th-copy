const red = '#ed224b';
const yellow = '#ffd624';
const darkBlue = '#232c7b';
const grey = '#b3b3b0';
const xmlns = "http://www.w3.org/2000/svg";


// const trackRecordData = [
//     //needs to be sorted from newest to oldest
//     {
//         parsha: 'ויצא',
//         won: false,
//         prize: 'Cap',
//         days: [{ completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: false, past: false }, { completed: false, past: false }, { completed: false, past: false }]
//     }, {
//         parsha: 'תולדות',
//         won: false,
//         prize: 'T Shirt',
//         days: [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
//     }, {
//         parsha: 'חיי שרה',
//         won: false,
//         prize: 'Sweatshirt',
//         days: [{ completed: true, past: true }, { completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
//     }, {
//         parsha: 'וירא',
//         won: false,
//         prize: 'Mug',
//         days: [{ completed: true, past: true }, { completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
//     }, {
//         parsha: 'לך לך',
//         won: true,
//         prize: 'Fidget Spinner',
//         days: [{ completed: false, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
//     }, {
//         parsha: 'נח',
//         won: false,
//         prize: 'Mug',
//         days: [{ completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
//     }, {
//         parsha: 'בראשית',
//         won: false,
//         prize: 'Sweatshirt',
//         days: [{ completed: true, past: true }, { completed: false, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }, { completed: true, past: true }]
//     }
// ];

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
    loadImage(prizeImg, currentWeekData.img, currentWeekData.thumb, currentWeekData.name)

    prizeDetails.appendChild(raffleFlagContainer);
    prizeDetails.appendChild(p);
    prizeDetails.appendChild(prizeTitle);
    prizeDetails.appendChild(prizeImg);

    new Chart('raffle5Doughnut', {
        // The type of chart we want to create
        type: 'doughnut',

        // The data for our dataset
        data: {
            datasets: [{
                data: [100],
                backgroundColor: [red],
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
}


const createSmallDonutChart = (donutData, index) => {
    let trackDataDiv = document.getElementById('trackRecord' + index).getContext('2d');
    let container = document.getElementById('trackRecord' + index).parentElement;
    container.classList.add('raffleInfo');

    //Load Parsha
    let parsha = document.createElement('p');
    parsha.classList.add('parsha');
    parsha.innerText = donutData.parsha;
    container.prepend(parsha);

    //Load small flag container
    let amountCompleted = donutData.days.filter(d => d.completed).length;
    let smallRaffleFlagContainer = document.createElement('div');
    smallRaffleFlagContainer.classList.add('smallRaffleFlagContainer');
    let img = document.createElement('img');
    img.src = amountCompleted > 4 ? '/mobile/reg/images/5-flag-plain.png' : '/mobile/reg/images/5-flag-plain-empty.png';
    img.alt = 'flag';
    let h2 = document.createElement('h2');
    h2.innerText = amountCompleted;
    smallRaffleFlagContainer.appendChild(h2);
    smallRaffleFlagContainer.appendChild(img);
    container.appendChild(smallRaffleFlagContainer);

    //Load Doughnut chart
    const data = donutData.days.map(() => 100 / donutData.days.length);
    new Chart(trackDataDiv, {
        // The type of chart we want to create
        type: 'doughnut',

        // The data for our dataset
        data: {
            datasets: [{
                data: donutData.won ? [100] : data,
                backgroundColor: donutData.won ? [red] : donutData.days.map(day => day.completed ? red : day.past ? grey : '#ed224b1a'),
                borderWidth: donutData.won ? 0 : 2,
            }]
        },

        // Configuration options go here
        options: {
            cutoutPercentage: 75,
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

    if (donutData.won) {
        //Yellow background
        let winningCircle = document.createElement('div');
        winningCircle.classList.add('winningCircle');

        container.appendChild(winningCircle);

        //Congrats text. TODO: responsive size based on size of doughnut
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

        container.appendChild(textSVG);
    }

    return container;
};

const loadDonutCharts = () => {
    const trackRecordContainer = document.getElementById("trackRecordContainer");
    let index = 0;
    let div;
    trackRecordData.forEach((trackRecord, index) => {
        if (index % 3 === 0) {
            if (div) trackRecordContainer.appendChild(div);
            div = document.createElement('div');
        }
        let smallDonutChart = createSmallDonutChart(trackRecord, index);
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
    const id = new URLSearchParams(window.location.search).get('id');
    document.getElementById('loader').remove();
    if (pastWinnersData.previous) {
        let previousWinnerParsha = document.getElementById('previousWinnerParsha');
        previousWinnerParsha.onclick = () => fetchPastWinners(pastWinnersData.previous.id, id);
        previousWinnerParsha.innerText = `<< ${pastWinnersData.previous.name}`
    }
    if (pastWinnersData.next) {
        let nextWinnerParsha = document.getElementById('nextWinnerParsha');
        nextWinnerParsha.onclick = () => fetchPastWinners(pastWinnersData.next.id, id);
        nextWinnerParsha.innerText = `${pastWinnersData.next.name} >>`
    }

    document.getElementById('parsha-header').innerText = `פרשת ${pastWinnersData.raffle.name}`;

    let parshaPrizeContainer = document.getElementById('parshaPrizeContainer');
    let img = document.createElement('img');
    let prize = pastWinnersData.raffle.winner_info.boys[0] ? pastWinnersData.raffle.winner_info.boys[0] : pastWinnersData.raffle.winner_info.girls[0];
    loadImage(img, prize.prize_picture.full, prize.prize_picture.thumb, prize.prize_name)
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

const loadImage = (elm, img, thumb, name) => {
    let w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
    let pic_src = ((w > 850) || !thumb) ? img : thumb;
    if (pic_src) {
        elm.setAttribute('src', `//mashpia.com/${encodeURI(pic_src)}`);
        elm.setAttribute('alt', name);
    }
}

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

let trackRecordData = [];

const fetchTrackRecordData = async (user_id) => {
    try {
        let response = await axios.get(`/mobile/api/raffles/weekly.php?action=track-records&user_id=${user_id}`);
        if (response.data) {
            trackRecordData = response.data;
            loadDonutCharts();
        }
    } catch (err) {
        console.log(err)
    }
};

let currentWeekData = {
    img: '',
    name: '',
    thumb: ''
}

const fetchCurrentWeekData = async (raffle_id, user_id) => {
    try {
        let response = await axios.get(`/mobile/api/raffles/weekly.php?action=prize-info&user_id=${user_id}`);
        if (response.data) {
            currentWeekData = response.data;
            createBigDonutChart();
        }
    } catch (err) {
        console.log(err)
    }
};

let pastWinnersData = {
    next: {},
    previous: {},
    raffle: {}
}

let pastWinnersDataLoading = true;
let raffle_id;

const fetchPastWinners = async (raffle_id, user_id) => {
    clearPastWinners();
    pastWinnersDataLoading = true;
    try {
        let response = await axios.get('/mobile/news/api/raffle.php?raffle_id=' + raffle_id);
        if (response.data && response.data.success) {
            raffle_id = response.data.data.raffle.raffle_id;
            let response2 = await axios.get(`/mobile/api/raffles/weekly.php?action=completed&user_id=${user_id}&raffle_id=${raffle_id}`);
            pastWinnersData = response.data.data;
            pastWinnersData.raffle.days_completed = response2.data.days_completed; //TODO: get from backend
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

    fetchTrackRecordData(id);
    fetchCurrentWeekData(0, id);
    fetchPastWinners(0, id);
}
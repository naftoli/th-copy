const red = '#e92d41';
const yellow = '#ffd942';
const darkBlue = '#1b2b51';
const grey = '#b3b3b0';

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

    let trackDataDiv = document.getElementById('trackRecord' + flag).getContext('2d');
    let container = document.getElementById('trackRecord' + flag).parentElement;
    container.classList.add('raffleInfo');
    container.onclick = function () { window.location.href = `/mobile/reg/raffle${flag}.html?id=${id}` };

    //Load Doughnut chart
    new Chart(trackDataDiv, {
        // The type of chart we want to create
        type: 'doughnut',

        // The data for our dataset
        data: {
            datasets: [{
                data: [(flag - data.daysLeft) / flag * 100, 100 - ((flag - data.daysLeft) / flag * 100)],
                backgroundColor: [stroke, grey],
                borderWidth: 0,
            }]
        },

        // Configuration options go here
        options: {
            cutoutPercentage: 80,
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


    let raffleId = createElementWithClass('div', 'raffleID');
    raffleId.classList.add('item');

    let raffleFlagContainer = createElementWithClass('div', 'raffleFlagContainer');
    let img = document.createElement('img');
    img.setAttribute('src', flagSrc);
    img.setAttribute('alt', 'flag');
    raffleFlagContainer.appendChild(img);

    let raffleDetails = createElementWithClass('div', 'raffleDetails');
    let daysLeft = createElementWithClass('p', 'daysLeft');
    daysLeft.style.color = stroke;
    daysLeft.innerText = data.daysLeft === 0 ? 'MAZAL TOV!' : `${data.daysLeft} MORE DAYS`;
    let daysLeft2 = document.createElement('p');
    let raffleName = flag === 5 ? `the ${data.raffleName} raffle` : data.raffleName;
    daysLeft2.innerText = data.daysLeft === 0 ? `you have been entered into ${raffleName}` : `of missions to enter ${raffleName}`;
    let details = createElementWithClass('p', 'details');
    details.innerText = 'details >>';
    raffleDetails.appendChild(daysLeft);
    raffleDetails.appendChild(daysLeft2);
    raffleDetails.appendChild(details);

    raffleId.appendChild(raffleFlagContainer);
    container.appendChild(raffleId);
    container.appendChild(raffleDetails);

    return container;
};

let raffleNavData = {
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

const fetchRaffleData = async (user_id) => {
    try {
        let response = await axios.get(`/mobile/api/raffles/dashboard.php?user_id=${user_id}`);
        if (response.data) {
            raffleNavData = response.data;

            const raffleInfoContainer = document.getElementById('raffleInfoContainer');

            let raffle5 = loadRaffleContainer(5, raffleNavData.raffle5);
            let raffle60 = loadRaffleContainer(60, raffleNavData.raffle60);
            let raffle180 = loadRaffleContainer(180, raffleNavData.raffle180);

            raffleInfoContainer.appendChild(raffle5);
            raffleInfoContainer.appendChild(raffle60);
            raffleInfoContainer.appendChild(raffle180);
        }
    } catch (err) {
        console.log(err)
    }
};


document.addEventListener("DOMContentLoaded", function (event) {
    const id = new URLSearchParams(window.location.search).get('id');

    fetchRaffleData(id);
});

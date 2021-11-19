import React from 'react';
import { createUseStyles } from 'react-jss';
// import clsx from 'clsx';
import colors from './colors';
import header from 'img/reportCards/reportCardHeaderBW.png';
import footer from 'img/reportCards/footer.JPG';
// import bwheader from 'img/reportCards/bwReportCardHeader.png';
import './ReportCard.css';
import axios from "axios";

const useStyles = createUseStyles(theme => ({
    root: {
        boxSizing: 'border-box',
        size: '11in 8.5in',
        height: '8.5in',
        width: '11in',
        // background: colors.blue,
        // padding: '60px 40px',
        // display: 'flex',
        // flexDirection: 'column',
    },
    bwRoot: {
        background: `${colors.white} !important`
    },
    bwText: {
        color: `${colors.black} !important`
    },
    bwBorderBottom: {
        borderBottom: `1px solid ${colors.black} !important`,
        '&:last-child': {
            borderBottom: 'none !important'
        }
    },
    bwBorderRight: {
        borderRight: `1px solid ${colors.black} !important`,
        color: `${colors.black} !important`,
        '&:first-child': {
            color: `${colors.black} !important`
        },
        '&:last-child': {
            borderRight: 'none !important'
        }
    },
    bwBorder: {
        border: `2px solid ${colors.black} !important`
    },
    content: {
        flex: 1,
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        paddingBottom: 10
    },
    name: {
        fontFamily: 'Bould Bold',
        fontSize: 22,
        marginTop: 30,
        marginBottom: 0
    },
    grade: {
        fontFamily: 'Bould Bold',
        fontSize: 20,
        marginBottom: 0
    },
    tableContainer: {
        border: `2px solid ${colors.darkBlue}`,
        borderRadius: 15,
        marginTop: 30,
        padding: 10,
        width: '100%',
        '& > table': {
            width: '100%'
        }
    },
    tableHead: {
        borderBottom: `1px solid ${colors.purple}`
    },
    tableRow: {
        borderBottom: `1px solid ${colors.purple}`,
        '&:last-child': {
            borderBottom: 'none'
        }
    },
    tableHeader: {
        color: colors.purple,
        padding: 10,
        textAlign: 'center',
        borderRight: `1px solid ${colors.purple}`,
        '&:last-child': {
            borderRight: 'none'
        }
    },
    tableData: {
        borderRight: `1px solid ${colors.purple}`,
        padding: 10,
        textAlign: 'center',
        fontFamily: 'Bould Bold',
        '&:first-child': {
            color: colors.purple,
            fontFamily: 'Bould Bold',
        },
        '&:last-child': {
            borderRight: 'none'
        }
    },
    avgRequiredContainer: {
        display: 'flex',
        marginTop: 30
    },
    placeholder: {
        flex: 1
    },
    description: {
        marginBottom: 0,
        color: colors.purple,
        fontSize: 12,
        fontFamily: 'Bould',
        flex: 5,
        marginRight: 10,
        marginLeft: 10,
        textAlign: 'right'
    },
    averageDuration: {
        padding: 8,
        border: `2px solid ${colors.darkBlue}`,
        fontFamily: 'Bould Bold',
        borderRadius: 15,
        fontSize: 18,
        flex: 1,
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center'
    },
    h5: {
        fontWeight: 'bold',
        marginTop: 30,
        fontSize: '1.5rem',
        textAlign: 'center'
    },
    lastRow: {
        backgroundColor: '#D3D3D3',
        verticalAlign: 'baseline'
    },
    finalRow: {
        backgroundColor: '#D3D3D3',
        textAlign: 'left',
    },
    card: {
        textAlign: 'center',
        fontSize: '12px',
        flex: '1'
    },
    unbold: {
        fontWeight: 'normal'
    },
    header: {
        margin: 'auto',
        textAlign: 'center',
        width: '500px',
        paddingBottom: '20px'
    }
}), { name: 'ReportCard' });


function ReportCard(info) {
    // console.log(info)
    const report = info.info
    const elem = report.user_id + '_rows'
    const numTests = info.testNum
    let mainStyle
    switch (numTests) {
        case 1:
            mainStyle = {'marginTop': '20%'}
            break
        case 2:
            mainStyle = {'marginTop': '16%'}
            break
        case 3:
            mainStyle = {'marginTop': '12%'}
            break
        case 4:
            mainStyle = {'marginTop': '8%'}
            break
        default:
            mainStyle = {'marginTop': '15%'}
            break
    }

    const classes = useStyles();

    const showIyun = !info.showIyun || (info.showIyun && report.tests[1]['genius'] >= 90)

    const learningTime = {
        'maven': 15,
        'pro': 30,
        'expert': 45,
        'genius': 60
    }

    const totals = {}
    for (let k of Object.keys(learningTime)) {
        totals[k] = 0
        for (let i = 1; i <= numTests; i++) {
            if (report.scores[i])
                totals[k] += parseInt(report.scores[i][k], 10)
        }
    }
    // console.log(totals)

    const totalDays = [32, 28, 30, 31]

    const state = {
        totals: []
    }

    const getTotals = async (perDay) => {
        const { data } = await axios.get(`https://mashpia.com/chidonTests/api/reportCards/getTotalLimmud.php?id=${report.user_id}&test=${numTests}`);
        state.totals = data
        fillTable(perDay)
    }

    const fillTable = perDay => {
        if (! state.totals.length) {
            getTotals(perDay)
        } else {
            let html = ''
            state.totals.map((total, i) => {
                let totalMinutes = totalDays[i] * perDay
                let totalHours = totalMinutes / 60
                if (! Number.isInteger(totalHours)) totalHours = totalHours.toFixed(2)
                let loggedHours = total / 60
                if (! Number.isInteger(loggedHours)) loggedHours = loggedHours.toFixed(2)
                html += `<tr><td>${i + 1}</td><td>${totalMinutes} Minutes = ${totalHours} Hours</td><td>${total} Minutes = ${loggedHours} Hours</td></tr>`
                return ''
            })
            let e = document.getElementById(elem)
            if (e) e.innerHTML = html
        }
    }

    let rows = []
    for (let i = 1; i <= numTests; i++) {
        rows.push(i)
    }

    let totalMarks = {}
    totalMarks['maven'] = totals['maven'] ? (totals['maven'] / (report.questions['maven'] * numTests) * 100) : 0
    totalMarks['pro'] = totals['pro'] ? ((totals['maven'] + totals['pro']) /
        ((parseInt(report.questions['maven'], 10) + parseInt(report.questions['pro'], 10)) * numTests) * 100) : 0
    totalMarks['expert'] = totals['expert'] ? ((totals['maven'] + totals['pro'] + totals['expert']) /
        ((parseInt(report.questions['maven'], 10) + parseInt(report.questions['pro'], 10) + parseInt(report.questions['expert'], 10)) * numTests) * 100) : 0
    totalMarks['genius'] = totals['genius'] ? ((totals['maven'] + totals['pro'] + totals['expert'] + totals['genius']) /
        ((parseInt(report.questions['maven'], 10) + parseInt(report.questions['pro'], 10) + parseInt(report.questions['expert'], 10) + parseInt(report.questions['genius'], 10)) * numTests) * 100) : 0
    for (let k of Object.keys(learningTime)) {
        if (totalMarks[k] % 1) totalMarks[k] = totalMarks[k].toFixed(2)
    }

    return (
        <div className="main" style={mainStyle}>
            <div className="container">
                <div className={classes.card} style={{paddingRight: '20px'}}>
                    <img src={header} alt="Header" className={classes.header} />
                    <br /><br />
                    <p><b>Name:</b> {report.name}</p>
                    <p><b>School:</b> {report.school}  <b>Class:</b> {report.grade}</p>
                    <br />
                    <p><b>Track you are on:</b> {report.currentTrack}</p>
                    <p><b>Highest track you passed:</b> {report.highestTrackPassed}</p>
                    <br />
                    <p><b>Learning commitment per day:</b> {learningTime[report.track]} minutes</p>
                    <br /><br />
                    <table className={classes.table}>
                        <thead>
                            <tr>
                                <th>Test</th>
                                <th>Total commited Learning Time</th>
                                <th>Amount of time you Logged</th>
                            </tr>
                        </thead>
                        <tbody id={elem}>
                            {fillTable(learningTime[report.track])}
                        </tbody>
                    </table>
                </div>
                <table className="mainTable">
                    <thead>
                        <tr>
                            <th>Test #</th>
                            <th>Questions / Mark</th>
                            <th>Yesod<br /><span className={classes.unbold}>Part 1</span></th>
                            <th>Yediah<br /><span className={classes.unbold}>Parts 1 and 2</span></th>
                            <th>Havonah<br /><span className={classes.unbold}>Parts 1, 2 and 3</span></th>
                            {showIyun &&
                                <th>Iyun<br/><span className={classes.unbold}>Parts 1 - 4</span></th>
                            }
                        </tr>
                    </thead>
                    <tbody>
                        {rows.map(index => (
                            <React.Fragment>
                                <tr>
                                    <td rowSpan={2}>{index}</td>
                                    <td>Correct Questions</td>
                                    <td>{report.scores[index] ? report.scores[index]['maven'] : 0} / {report.questions['maven']}</td>
                                    <td>{report.scores[index] ? (parseInt(report.scores[index]['pro'], 10) + parseInt(report.scores[index]['maven'], 10)) : 0} /
                                        {report.questions['pro'] + report.questions['maven']}</td>
                                    <td>{report.scores[index] ? (parseInt(report.scores[index]['expert'], 10) + parseInt(report.scores[index]['pro'], 10) + parseInt(report.scores[index]['maven'], 10)) : 0} /
                                        {report.questions['expert'] + report.questions['pro'] + report.questions['maven']}</td>
                                    {showIyun &&
                                    <td>{report.scores[index] ? (parseInt(report.scores[index]['genius'], 10) + parseInt(report.scores[index]['expert'], 10) +
                                    parseInt(report.scores[index]['pro'], 10) + parseInt(report.scores[index]['maven'], 10)) : 0} /
                                        {report.questions['genius'] + report.questions['expert'] + report.questions['pro'] + report.questions['maven']}</td>
                                    }
                                </tr>
                                <tr>
                                    <td>Mark</td>
                                    <td>{report.scores[index] ? (report.tests[index]['maven'] % 1 ? report.tests[index]['maven'].toFixed(2) : report.tests[index]['maven']) : 0}%</td>
                                    <td>{report.scores[index] ? (report.tests[index]['pro'] % 1 ? report.tests[index]['pro'].toFixed(2) : report.tests[index]['pro']) : 0}%</td>
                                    <td>{report.scores[index] ? (report.tests[index]['expert'] % 1 ? report.tests[index]['expert'].toFixed(2) : report.tests[index]['expert']) : 0}%</td>
                                    {showIyun &&
                                    <td>{report.scores[index] ? (report.tests[index]['genius'] % 1 ? report.tests[index]['genius'].toFixed(2) : report.tests[index]['genius']) : 0}%</td>
                                    }
                                </tr>
                            </React.Fragment>
                        ))}
                        <tr>
                            <td rowSpan={2}>Total</td>
                            <td>Correct Questions</td>
                            <td>{totals['maven']} / {report.questions['maven'] * numTests}</td>
                            <td>{totals['maven'] + totals['pro']} / {
                                (parseInt(report.questions['maven'], 10) + parseInt(report.questions['pro'], 10)) * numTests}
                            </td>
                            <td>{totals['maven'] + totals['pro'] + totals['expert']} / {
                                (parseInt(report.questions['maven'], 10) + parseInt(report.questions['pro'], 10) + parseInt(report.questions['expert'], 10)) * numTests}</td>
                            {showIyun &&
                                <td>{totals['maven'] + totals['pro'] + totals['expert'] + totals['genius']} / {
                                    (parseInt(report.questions['maven'], 10) + parseInt(report.questions['pro'], 10) + parseInt(report.questions['expert'], 10) + parseInt(report.questions['genius'], 10)) * numTests}</td>
                            }
                        </tr>
                        <tr>
                            <td>Mark</td>
                            <td>{totalMarks['maven']}%</td>
                            <td>{totalMarks['pro']}%</td>
                            <td>{totalMarks['expert']}%</td>
                            {showIyun &&
                                <td>{totalMarks['genius']}%</td>
                            }
                        </tr>
                        <tr className={classes.lastRow}>
                            <td colSpan={2}>Passing Mark for Reward</td>
                            <td>70%<br />Sweater & gift</td>
                            <td>70%<br />Sweater, Gift & Prizes</td>
                            <td>70%<br />Sweater, Gift, Prizes & Regional Trip</td>
                            {showIyun &&
                                <td>90%<br/>Sweater, Gift, Prizes, Regional Trip & Trophy Contestant</td>
                            }
                        </tr>
                        <tr className={classes.finalRow}>
                            <td colSpan={6} style={{padding: '10px'}}>
                                Please Note: The track that you are on is just to give you an idea of:<br />
                                <ul>
                                    <li>How much time you have committed to learn.</li>
                                    <li>How well you are going to know the information.</li>
                                    <li>What prizes you are going for.</li>
                                </ul>
                                But it does not impact the prize you will earn. No matter the track you chose,
                                you will receive the rewards for the levels you pass.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h5 className={classes.h5}>!מחיל אל חיל</h5>
            <img src={footer} alt="Footer" />
        </div>
    );
}

export default ReportCard;

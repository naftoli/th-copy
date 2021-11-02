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
        size: '5.5in 8.5in',
        height: '8.5in',
        width: '5.5in',
        background: colors.blue,
        padding: '60px 40px',
        display: 'flex',
        flexDirection: 'column',
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
        marginBottom: 20
    },
    lastRow: {
        backgroundColor: '#D3D3D3'
    },
    card: {
        textAlign: 'center',
        fontSize: '12px',
        boxSizing: 'border-box',
        size: '5in 8.5in',
        width: '5in',
        pageBreakAfter: 'always'
    },
    unbold: {
        fontWeight: 'normal'
    }
}), { name: 'ReportCard' });


function ReportCard(info) {
    // console.log(info)
    const report = info.info

    const classes = useStyles();

    // const [tests, setTests] = useState([]);

    // useEffect(() => {
    //     let newTests = [report.tests]
    //     //     .filter(test => (
    //     //     typeof test.mivtzahMaven === "number" ||
    //     //     typeof test.shabbatonMark === "number"
    //     // ));
    //     setTests(newTests);
    // }, [report.tests]);

    const showIyun = report.tests[1]['genius'] >= 90
    const learningTime = {
        'maven': 15,
        'pro': 30,
        'expert': 45,
        'genius': 60
    }
    const totalTime = {
        'maven': 480,
        'pro': 960,
        'expert': 1440,
        'genius': 1920
    }

    const state = {
        total: ''
    }

    const getTotal = async () => {
        const { data } = await axios.get(`https://mashpia.com/chidonTests/api/reportCards/getTotalLimmud.php?id=${report.user_id}`);
        state.total = data
        document.getElementById(report.user_id).innerText = data + ' minutes'
    }

    if (state.total === '') getTotal()

    const totalSpan = report.user_id

    return (
        <div className={classes.card}>
            <br /><br />
            <img src={header} alt="Header" />
            <p></p><br />
            <p><b>Name:</b> {report.name}</p>
            <p><b>School:</b> {report.school}  <b>Class:</b> {report.grade}</p>
            <p>
                <b>Track you are on:</b> {report.currentTrack}<br />
                <b>Learning commitment per day:</b> {learningTime[report.track]} minutes<br />
                <b>Total test 1 learning time:</b> {totalTime[report.track]} minutes<br />
                <b>Amount of time you logged:</b> <span id={totalSpan}></span>
            </p>
            <p><b>Highest track you passed:</b> {report.highestTrackPassed}</p>
            <table className={classes.table}>
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
                    <tr>
                        <td rowSpan={2}>1</td>
                        <td>Correct Questions</td>
                        <td>{report.scores[1]['maven']} / {report.questions['maven']}</td>
                        <td>{parseInt(report.scores[1]['pro'], 10) + parseInt(report.scores[1]['maven'], 10)} /
                            {report.questions['pro'] + report.questions['maven']}</td>
                        <td>{parseInt(report.scores[1]['expert'], 10) + parseInt(report.scores[1]['pro'], 10) + parseInt(report.scores[1]['maven'], 10)} /
                            {report.questions['expert'] + report.questions['pro'] + report.questions['maven']}</td>
                        {showIyun &&
                            <td>{parseInt(report.scores[1]['genius'], 10) + parseInt(report.scores[1]['expert'], 10) +
                                    parseInt(report.scores[1]['pro'], 10) + parseInt(report.scores[1]['maven'], 10)} /
                                {report.questions['genius'] + report.questions['expert'] + report.questions['pro'] + report.questions['maven']}</td>
                        }
                    </tr>
                    <tr>
                        <td>Mark</td>
                        <td>{report.tests[1]['maven'] % 1 ? report.tests[1]['maven'].toFixed(2) : report.tests[1]['maven']}%</td>
                        <td>{report.tests[1]['pro'] % 1 ? report.tests[1]['pro'].toFixed(2) : report.tests[1]['pro']}%</td>
                        <td>{report.tests[1]['expert'] % 1 ? report.tests[1]['expert'].toFixed(2) : report.tests[1]['expert']}%</td>
                        {showIyun &&
                            <td>{report.tests[1]['genius'] % 1 ? report.tests[1]['genius'].toFixed(2) : report.tests[1]['genius']}%</td>
                        }
                    </tr>
                    <tr className={classes.lastRow}>
                        <td colSpan={2}>Passing Mark for Reward</td>
                        <td>70%<br />Sweater & gift</td>
                        <td>70%<br />Sweater, Gift & Prizes</td>
                        <td>70%<br />Sweater, Gift, Prizes & Trip</td>
                        {showIyun &&
                            <td>90%<br/>Sweater, Gift, Prizes, Trip & Trophy Contestant</td>
                        }
                    </tr>
                </tbody>
            </table>
            <h5 className={classes.h5}>!מחיל אל חיל</h5>
            <img src={footer} alt="Footer" />
        </div>
        // <div className={clsx(classes.root, bw && classes.bwRoot)}>
        //     <img src={bw ? bwheader : header} alt="Header" />
        //     <div className={classes.content}>
        //         <p className={classes.name}>{name}</p>
        //         <p className={classes.grade}>{grade}</p>
        //         <div className={clsx(classes.tableContainer, bw && classes.bwBorder)}>
        //             <table>
        //                 <thead className={clsx(classes.tableHead, bw && classes.bwBorderBottom)}>
        //                 <tr className={clsx(classes.tableRow, bw && classes.bwBorderBottom)}>
        //                     <th className={clsx(classes.tableHeader, bw && classes.bwBorderRight)}>
        //                         Test #
        //                     </th>
        //                     <th className={clsx(classes.tableHeader, bw && classes.bwBorderRight)}>
        //                         Mitzvah Maven <br /> Test Mark
        //                     </th>
        //                 </tr>
        //                 </thead>
        //                 <tbody>
        //                 {tests.map((test, index) => (
        //                     <tr key={index} className={clsx(classes.tableRow, bw && classes.bwBorderBottom)}>
        //                         <td className={clsx(classes.tableData, bw && classes.bwBorderRight)}>
        //                             {index + 1}
        //                         </td>
        //                         <td className={clsx(classes.tableData, bw && classes.bwBorderRight)}>
        //                             {Math.round(test.mivtzahMaven)}
        //                         </td>
        //                     </tr>
        //                 ))}
        //                 {tests.length > 1 && (
        //                     <tr className={clsx(classes.tableRow, bw && classes.bwBorderBottom)}>
        //                         <td className={clsx(classes.tableData, bw && classes.bwBorderRight)}>
        //                             Avg.
        //                         </td>
        //                         <td className={clsx(classes.tableData, bw && classes.bwBorderRight)}>
        //                             {Math.round(tests.reduce((a, b) => a + b.mivtzahMaven, 0) / tests.length)}
        //                         </td>
        //                     </tr>
        //                 )}
        //                 </tbody>
        //             </table>
        //         </div>
        //         {tests.length < 4 ? (
        //             <div className={classes.avgRequiredContainer}>
        //                 <div className={classes.placeholder} />
        //                 <p className={clsx(classes.description, bw && classes.bwText)}>
        //                     Average required on the next tests
        //                     <br /> to earn a place on the Chidon Shabbaton
        //                     (In whichever format it will be taking place this year):
        //                 </p>
        //                 <div className={clsx(classes.averageDuration, bw && classes.bwBorder)}>
        //                     {avgRequired}
        //                 </div>
        //             </div>
        //         ) : <h5 className={classes.h5}>!מחיל אל חיל</h5>
        //         }
        //     </div>
        //     <img src={footer} alt="Footer" />
        // </div>
    );
}

export default ReportCard;

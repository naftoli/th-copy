import React, { useEffect, useState } from 'react';
import { createUseStyles } from 'react-jss';
import clsx from 'clsx';
import colors from './colors';
import header from 'img/reportCards/reportCardHeader.png';
import footer from 'img/reportCards/reportCardFooter.png';
import bwheader from 'img/reportCards/bwReportCardHeader.png';

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
    }
}), { name: 'ReportCard' });


function ReportCard({ id, name, grade, avgRequired, tests: _tests, bw }) {
    const classes = useStyles();

    const [tests, setTests] = useState([]);

    useEffect(() => {
        let newTests = [..._tests].filter(test => (
            typeof test.mivtzahMaven === "number" ||
            typeof test.shabbatonMark === "number"
        ));
        setTests(newTests);
    }, [_tests]);

    return (
        <div className={clsx(classes.root, bw && classes.bwRoot)}>
            <img src={bw ? bwheader : header} alt="Header" />
            <div className={classes.content}>
                <p className={classes.name}>{name}</p>
                <p className={classes.grade}>{grade}</p>
                <div className={clsx(classes.tableContainer, bw && classes.bwBorder)}>
                    <table>
                        <thead className={clsx(classes.tableHead, bw && classes.bwBorderBottom)}>
                            <tr className={clsx(classes.tableRow, bw && classes.bwBorderBottom)}>
                                <th className={clsx(classes.tableHeader, bw && classes.bwBorderRight)}>
                                    Test #
                                </th>
                                <th className={clsx(classes.tableHeader, bw && classes.bwBorderRight)}>
                                    Mitzvah Maven <br /> Test Mark
                                </th>
                                <th className={clsx(classes.tableHeader, bw && classes.bwBorderRight)}>
                                    Shabbaton <br /> Test Mark
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            {tests.map((test, index) => (
                                <tr key={index} className={clsx(classes.tableRow, bw && classes.bwBorderBottom)}>
                                    <td className={clsx(classes.tableData, bw && classes.bwBorderRight)}>
                                        {index + 1}
                                    </td>
                                    <td className={clsx(classes.tableData, bw && classes.bwBorderRight)}>
                                        {Math.round(test.mivtzahMaven)}
                                    </td>
                                    <td className={clsx(classes.tableData, bw && classes.bwBorderRight)}>
                                        {Math.round(test.shabbatonMark)}
                                    </td>
                                </tr>
                            ))}
                            {tests.length > 1 && (
                                <tr>
                                    <td className={clsx(classes.tableData, bw && classes.bwBorderRight)}>
                                        Avg.
                                    </td>
                                    <td className={clsx(classes.tableData, bw && classes.bwBorderRight)}>
                                        {Math.round(tests.reduce((a, b) => a + b.mivtzahMaven, 0) / tests.length)}
                                    </td>
                                    <td className={clsx(classes.tableData, bw && classes.bwBorderRight)}>
                                        {Math.round(tests.reduce((a, b) => a + b.shabbatonMark, 0) / tests.length)}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <div className={classes.avgRequiredContainer}>
                    <div className={classes.placeholder} />
                    <p className={clsx(classes.description, bw && classes.bwText)}>
                        Average required on the next tests
                        <br /> to earn a place on the Chidon Shabbaton
                        (In whichever format it will be taking place this year):
                    </p>
                    <div className={clsx(classes.averageDuration, bw && classes.bwBorder)}>
                        {avgRequired}
                    </div>
                </div>
            </div>
            <img src={footer} alt="Footer" />
        </div>
    );
}

export default ReportCard;

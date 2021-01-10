import React, { useEffect, useState, useCallback } from 'react';
import { createUseStyles } from 'react-jss';
import ReportCard from './ReportCard';
import axios from 'axios';
import colors from './colors';
import clsx from 'clsx';

const useStyles = createUseStyles(theme => ({
    reports: {
        boxSizing: 'border-box',
        display: 'flex',
        flexWrap: 'wrap'
    },
    toggle: {
        display: 'inline-flex',
        margin: '0 0 10px',
        borderRadius: 4,
        cursor: 'pointer',
        '@media print': {
            display: 'none'
        }
    },
    toggledOn: {
        color: colors.white,
        background: colors.darkBlue,
        padding: '5px 10px',
        fontSize: 12,
        transition: 'background 0.2s',
        border: `1px solid ${colors.darkBlue}`,
        borderRadius: 4
    },
    toggledOff: {
        color: '#aaa',
        background: 'transparent',
        padding: '5px 10px',
        fontSize: 12,
        transition: 'background 0.2s',
        border: '1px solid #dbdbdb',
        borderRadius: 4
    },
    leftToggle: {
        borderRight: 'none',
        borderTopRightRadius: 0,
        borderBottomRightRadius: 0
    },
    rightToggle: {
        borderLeft: 'none',
        borderTopLeftRadius: 0,
        borderBottomLeftRadius: 0
    }
}), { name: 'ReportCards' });

const ReportCards = (props) => {
    const { location: { search } } = props;

    const classes = useStyles();
    const [reports, setReports] = useState([]);
    const [bw, setBw] = useState(false);

    const toggleBW = useCallback(() => setBw(!bw), [bw]);

    useEffect(() => {
        const searchParams = new URLSearchParams(search);
        const test = searchParams.get('test');
        const user_id = searchParams.get('user_id');
        const class_id = searchParams.get('class_id');
        const school_id = searchParams.get('school_id');

        const fetchReportCards = async () => {
            const { data, status } = await axios.get(`http://mashpia.com/chidonTests/api/reportCards/?test=${test}&school_id=${school_id}&class_id=${class_id}&user_id=${user_id}`);
            if (status === 200) {
                setReports(data);
            }
        };

        if (test) fetchReportCards();

    }, [search])

    return (
        <div>
            <div className={classes.toggle} onClick={toggleBW}>
                <div className={clsx(!bw ? classes.toggledOn : classes.toggledOff, classes.leftToggle)}>Colored</div>
                <div className={clsx(bw ? classes.toggledOn : classes.toggledOff, classes.rightToggle)}>Black and White</div>
            </div>
            <div className={classes.reports}>

                {reports.map(({ id, name, grade, avgRequired, tests }) => (
                    <ReportCard
                        key={id}
                        name={name}
                        grade={grade}
                        avgRequired={avgRequired}
                        tests={tests}
                        bw={bw}
                    />
                ))}
            </div>
        </div>
    );
};

export default ReportCards;



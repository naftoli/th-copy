import React, { useEffect, useState, useCallback, useMemo } from 'react';
import { connect } from 'react-redux';
import { createUseStyles } from 'react-jss';
import clsx from 'clsx';
import axios from 'axios';
import colors from './colors';
import ReportCard from './ReportCard';
import { Row, Col } from 'reactstrap';
import { LoadingScreen } from 'components/ui';
import { isAdmin, isBC } from 'functions/login';
import { Select } from 'components/selects/static/Select';
import {
    PlatoonSelect,
    SoldierSelect,
    BaseSelect,
} from 'components/inputs';

const useStyles = createUseStyles(theme => ({
    reports: {
        boxSizing: 'border-box',
        display: 'flex',
        flexWrap: 'wrap'
    },
    toggle: {
        display: 'inline-flex',
        margin: '20px 0 10px',
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
    },
    selects: {
        '@media print': {
            display: 'none'
        }
    }
}), { name: 'ReportCards' });

const ReportCards = (props) => {
    const { login: { school_id, class_id, code } } = props;

    const classes = useStyles();
    const [bw, setBw] = useState(false);
    const [reports, setReports] = useState([]);
    const [loading, setLoading] = useState(false);
    const [test, setTest] = useState();
    const [userId, setUserId] = useState('-1');
    const [classId, setClassId] = useState(class_id || '-1');
    const [schoolId, setSchoolId] = useState(school_id || '-1');

    const toggleBW = useCallback(() => setBw(!bw), [bw]);

    useEffect(() => {
        const fetchReportCards = async () => {
            setLoading(true);
            const { data, status } = await axios.get(`http://mashpia.com/chidonTests/api/reportCards/?test=${test}&school_id=${schoolId}&class_id=${classId}&user_id=${userId}`);
            if (status === 200) {
                setReports(data);
            }
            setLoading(false);
        };

        if (test) fetchReportCards();

    }, [test, userId, classId, schoolId])

    const handleUpdateSchool = (selected) => {
        setSchoolId(selected.value || null);
    };

    const handleUpdateUser = (selected) => {
        setUserId(selected.value || '-1')
    };

    const handleUpdateClass = (selected) => {
        setClassId(selected.value || null)
    };

    const handleUpdateTest = (selected) => {
        setTest(selected.value || null);
    };

    const testOptions = useMemo(() => [
        { label: '1', value: 1 },
        { label: '2', value: 2 },
        { label: '3', value: 3 },
        { label: '4', value: 4 }
    ], []);

    return (
        <div>
            <Row className={classes.selects}>
                <Col sm={6}>
                    <label>Base</label>
                    <BaseSelect
                        placeholder='Select School...'
                        name='school_id'
                        onChange={handleUpdateSchool}
                        value={schoolId}
                        isDisabled={!isAdmin(code)}
                    />
                    <input type='hidden' value={schoolId} name='school_id' />
                </Col>

                <Col sm={6}>
                    <label>Platoon</label>
                    <PlatoonSelect
                        placeholder='Select Platoon...'
                        value={classId}
                        isDisabled={!isBC(code)}
                        openMenuOnFocus={false}
                        schoolId={schoolId}
                        onChange={handleUpdateClass}
                    />
                </Col>

                <Col sm={6}>
                    <label>Soldier</label>
                    <SoldierSelect
                        value={userId}
                        registeredOnly
                        schoolId={schoolId}
                        classIds={[classId]}
                        openMenuOnFocus={false}
                        onChange={handleUpdateUser}
                        placeholder='Select Soldier...'
                    />
                </Col>

                <Col sm={6}>
                    <label>Test</label>
                    <Select
                        openMenuOnFocus={false}
                        onChange={handleUpdateTest}
                        options={testOptions}
                        placeholder='Select Test...'
                    />
                </Col>
            </Row>

            {loading
                ? (
                    <LoadingScreen hideLogo />
                ) : (
                    <React.Fragment>
                        <div className={classes.toggle} onClick={toggleBW}>
                            <div className={clsx(!bw ? classes.toggledOn : classes.toggledOff, classes.leftToggle)}>
                                Colored
                        </div>
                            <div className={clsx(bw ? classes.toggledOn : classes.toggledOff, classes.rightToggle)}>
                                Black and White
                        </div>
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
                    </React.Fragment>
                )}
        </div>
    );
};

const mapStateToProps = ({ login, missions }) => {
    return {
        login: login.current_login,
    }
};

const mapDispatchToProps = {};

export default connect(mapStateToProps, mapDispatchToProps)(ReportCards);



import React, { useState, useCallback, useMemo } from 'react';
import { connect } from 'react-redux';
import { createUseStyles } from 'react-jss';
// import clsx from 'clsx';
import axios from 'axios';
import colors from './colors';
import ReportCard from './ReportCard2';
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
        // boxSizing: 'border-box',
        // display: 'flex',
        // flexWrap: 'wrap'
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
        '& > div > .Select': {
            cursor: 'pointer',
            '& > div > div': {
                cursor: 'pointer',
            }
        },
        '@media print': {
            display: 'none'
        }
    },
    generateButton: {
        margin: 'auto',
        marginTop: 15,
        borderRadius: 4,
        padding: '5px 20px',
        background: colors.white,
        color: colors.darkBlue,
        border: '1px solid',
        transition: 'background 0.2s',
        '&:focus': {
            outline: 0,
        },
        '&:disabled': {
            cursor: 'no-drop',
            background: '#e4e4e4',
            color: '#b9b9b9',
            border: 'none',
            transition: 'background 0.2s'
        }
    },
    instructions: {
        fontSize: 14,
        background: '#f2f2f2',
        width: '100%',
        margin: '15px 20px',
        borderRadius: 4,
        padding: '20px 20px 20px 40px',
        '& > span': {
            marginLeft: '-15px',
            marginTop: 10,
            display: 'inline-block',
            '&:first-child': {
                marginTop: 0,
                marginBottom: 10,
                fontWeight: 500,
                textTransform: 'uppercase',
                color: '#909090',
            }
        }
    },
    iyun: {
        marginTop: 15,
        textAlign: 'center'
    },
    checkbox: {
        marginRight: 5
    }
}), { name: 'ReportCards' });

const ReportCards = (props) => {
    const { login: { school_id, class_id, code } } = props;

    const classes = useStyles();
    // const [bw, setBw] = useState(false);
    const [reports, setReports] = useState([]);
    const [loading, setLoading] = useState(false);
    // const [reportsGenerated, setReportsGenerated] = useState(false);
    const [test, setTest] = useState();
    const [userId, setUserId] = useState('-1');
    const [classId, setClassId] = useState(class_id || '-1');
    const [schoolId, setSchoolId] = useState(school_id || '-1');
    const [iyun, setIyun] = useState(false)
    const [currentOnly, setCurrentOnly] = useState(false)

    // const toggleBW = useCallback(() => setBw(!bw), [bw]);

    const generateReports = useCallback(() => {
        const fetchReportCards = async () => {
            setLoading(true);
            const { data, status } = await axios.get(`https://mashpia.com/chidonTests/api/reportCards/?test=${test}&school_id=${schoolId || '-1'}&class_id=${classId || '-1'}&user_id=${userId || '-1'}`);
            if (status === 200) {
                setReports(data);
            }
            setLoading(false);
            // setReportsGenerated(true);
        };

        if (test) fetchReportCards();

    }, [test, userId, classId, schoolId])

    const handleUpdateSchool = (selected) => {
        setSchoolId((selected && selected.value) ? selected.value : '-1');
    };

    const handleUpdateUser = (selected) => {
        setUserId((selected && selected.value) ? selected.value : '-1')
    };

    const handleUpdateClass = (selected) => {
        setClassId((selected && selected.value) ? selected.value : '-1')
    };

    const handleUpdateTest = (selected) => {
        setTest((selected && selected.value) ? selected.value : null);
    };

    const handleUpdateIyun = event => {
        setIyun(event.target.checked)
    }

    const handleUpdateCurrentOnly = event => {
        setCurrentOnly(event.target.checked)
    }

    const testOptions = useMemo(() => [
        { label: 'Includes test 1', value: 1 },
        { label: 'Includes test 1 & 2', value: 2 },
        { label: 'Includes test 1, 2 & 3', value: 3 },
    ], []);

    return (
        <div>
            <Row className={classes.selects}>
                <ol className={classes.instructions}>
                    <span>Printing Instructions:</span>
                    <li>Press CTRL+P to open the Print dialog and wait for the preview to load</li>
                    <li>Ensure that the paper size is Letter, Layout is Landscape, and the Scale is set to 100%</li>
                    <span>It should show up nicely with one per page. Print and return to your Chayolim :)</span>
                </ol>
                <Col sm={6}>
                    <label>Base</label>
                    <BaseSelect
                        placeholder='All Schools'
                        isClearable
                        name='school_id'
                        value={schoolId}
                        isDisabled={!isAdmin(code)}
                        onChange={handleUpdateSchool}
                    />
                    <input type='hidden' value={schoolId} name='school_id' />
                </Col>

                <Col sm={6}>
                    <label>Platoon</label>
                    <PlatoonSelect
                        placeholder='All Platoons'
                        isClearable
                        registeredOnly
                        value={classId}
                        schoolId={schoolId}
                        openMenuOnFocus={false}
                        isDisabled={!isBC(code)}
                        onChange={handleUpdateClass}
                    />
                    <input type='hidden' value={classId} name='class_id' />
                </Col>

                <Col sm={6}>
                    <label>Soldier</label>
                    <SoldierSelect
                        isClearable
                        value={userId}
                        classId={classId}
                        schoolId={schoolId}
                        openMenuOnFocus={false}
                        onChange={handleUpdateUser}
                        placeholder='All Soldiers'
                    />
                    <input type='hidden' value={userId} name='user_id' />
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

                <Col sm={6} className={classes.iyun}>
                    <input type="checkbox" name="iyun" id="iyun" className={classes.checkbox} onClick={handleUpdateIyun} />
                    <label>Only show Iyun marks if child passed</label>
                </Col>

                <Col sm={6} className={classes.iyun}>
                    <input type="checkbox" name="currentOnly" id="currentOnly" className={classes.checkbox} onClick={handleUpdateCurrentOnly} />
                    <label>Only show current test</label>
                </Col>

                <button
                    disabled={!test}
                    onClick={generateReports}
                    className={classes.generateButton}
                >
                    Generate Report Cards
                </button>
            </Row>

            {loading
                ? (
                    <LoadingScreen hideLogo />
                ) : (
                    <React.Fragment>
                        {/*{reportsGenerated && (*/}
                        {/*    <div className={classes.toggle} onClick={toggleBW}>*/}
                        {/*        <div className={clsx(!bw ? classes.toggledOn : classes.toggledOff, classes.leftToggle)}>*/}
                        {/*            Colored*/}
                        {/*    </div>*/}
                        {/*        <div className={clsx(bw ? classes.toggledOn : classes.toggledOff, classes.rightToggle)}>*/}
                        {/*            Black and White*/}
                        {/*    </div>*/}
                        {/*    </div>*/}
                        {/*)}*/}

                        <div className={classes.reports}>

                            {reports.map(report => (
                                <ReportCard
                                    key={report.id}
                                    info={report}
                                    testNum={test}
                                    showIyun={iyun}
                                    current={currentOnly}
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



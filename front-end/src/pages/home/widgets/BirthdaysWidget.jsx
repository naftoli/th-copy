import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
// components
import { Row, Col } from 'reactstrap';
import { Link } from 'react-router-dom';
import { FontAwesome, Spinner, ProfilePicture } from 'components/ui';
// functions
import { toast } from 'react-toastify';
// constants

const Birthday = ({ profilePicture, user_id, name, class_id, platoon, age }) => {
  return (
    <Row className='Birthday'>
      <Col xs={3} xl={2}>
        <ProfilePicture src={profilePicture} />
      </Col>
      <Col xs={9} xl={10}>
        <Link to={`/bm/soldiers/${user_id}`}>{name} {age ? `(${age})` : ''}</Link><br />
        <Link to={`/bm/platoons/${class_id}`}>{platoon}</Link>
      </Col>
    </Row>
  )
}

export const BirthdaysWidget = ({ refresh, birthdays = {} }) => {

  const [showDateModal, setShowDateModal] = useState(false);
  const [fromDate, setFromDate] = useState('');
  const [toDate, setToDate] = useState('');
  const [appliedFromDate, setAppliedFromDate] = useState('');
  const [appliedToDate, setAppliedToDate] = useState('');
  const [isLoading, setIsLoading] = useState(false);

  const getDateString = (date) => {
    return date.toISOString().split('T')[0];
  }

  useEffect(() => {
    // Set default dates
    const defaultFromDate = getDateString(new Date());
    const defaultToDate = getDateString(new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)); // 7 days from now

    setFromDate(defaultFromDate);
    setToDate(defaultToDate);
    setAppliedFromDate(defaultFromDate);
    setAppliedToDate(defaultToDate);

    refresh(defaultFromDate, defaultToDate)
      .catch(error => toast.error(error.message));
  }, []); // eslint-disable-line react-hooks/exhaustive-deps


  const handleApply = () => {
    setShowDateModal(false);
    setAppliedFromDate(fromDate);
    setAppliedToDate(toDate);
    setIsLoading(true);

    refresh(fromDate, toDate)
      .then(() => setIsLoading(false))
      .catch(error => {
        setIsLoading(false);
        toast.error(error.message);
      });
  }

  const handleFromDateChange = (e) => {
    setFromDate(e.target.value);
  }

  const handleToDateChange = (e) => {
    setToDate(e.target.value);
  }

  const getDaysDifference = () => {
    if (!appliedFromDate || !appliedToDate) return 7;
    const fromDateObj = new Date(appliedFromDate);
    const toDateObj = new Date(appliedToDate);
    const diffTime = Math.abs(toDateObj - fromDateObj);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
  }

  let content;

  if (birthdays === false || isLoading) {
    content = <Spinner size={5} />
  } else if (Object.keys(birthdays).length === 0) {
    content = (
      <div className='no-data'>
        <FontAwesome icon='birthday-cake' />
        <p>No Upcoming Birthdays</p>
      </div>
    );
  } else {
    content = (
      <div className='birthdays'>
        {Object.entries(birthdays).map((date, index) => ( // for each date
          <div className='date' key={index}>
            <h3>{date[0]}</h3>
            {date[1].map((soldier, index) => <Birthday {...soldier} key={index} />)}
          </div>
        ))}
      </div>
    )
  }

  return (
    <Col xs={12} sm={6}>
      <div id='BirthdaysWidget' className='widget'>
        <h2>Upcoming Birthdays</h2>
        <div style={{ textAlign: 'center', backgroundColor: '#f0f0f0', position: 'relative' }}>
          <a style={{ color: 'blue', cursor: 'pointer' }} onClick={() => setShowDateModal(!showDateModal)}>Next {getDaysDifference()} days</a>
          {showDateModal && (
            <div className="modal-content" onClick={(e) => e.stopPropagation()} style={{
              position: 'absolute',
              top: '100%',
              left: '50%',
              transform: 'translateX(-50%)',
              maxWidth: '350px',
              padding: '15px',
              backgroundColor: 'white',
              borderRadius: '8px',
              boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
              zIndex: 1000,
              border: '1px solid #ddd'
            }}>
              <div className="row mb-3">
                <div className="col-6">
                  <label style={{ fontSize: '12px' }}>From:</label>
                  <input
                    type="date"
                    className="form-control form-control-sm"
                    value={fromDate}
                    onChange={handleFromDateChange}
                  />
                </div>
                <div className="col-6">
                  <label style={{ fontSize: '12px' }}>To:</label>
                  <input
                    type="date"
                    className="form-control form-control-sm"
                    value={toDate}
                    onChange={handleToDateChange}
                  />
                </div>
              </div>
              <div className="text-center">
                <button className="btn btn-primary btn-sm me-2" onClick={handleApply}>Apply</button>
                <button className="btn btn-secondary btn-sm" onClick={() => setShowDateModal(false)}>Cancel</button>
              </div>
            </div>
          )}
        </div>
        {content}
      </div>
    </Col>
  );
}

BirthdaysWidget.propTypes = {
  refresh: PropTypes.func.isRequired,
  birthdays: PropTypes.object
};

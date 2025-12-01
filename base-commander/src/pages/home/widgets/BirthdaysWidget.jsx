import React, { Component } from 'react';
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
        <ProfilePicture src={ profilePicture } />
      </Col>
      <Col xs={9} xl={10}>
        <Link to={`/bm/soldiers/${user_id}`}>{ name } { age ? `(${age})` : '' }</Link><br/>
        <Link to={`/bm/platoons/${class_id}`}>{ platoon }</Link>
      </Col>
    </Row>
  )
}

export class BirthdaysWidget extends Component {

  static propTypes = {
    refresh: PropTypes.func.isRequired,
    // birthdays: PropTypes.object.isRequired
  }

  static defaultProps = {
    birthdays: {},
  }

  state = {
    showDateModal: false,
    fromDate: '',
    toDate: '',
    appliedFromDate: '',
    appliedToDate: '',
    isLoading: false,
  }

  componentDidMount() {
    // Set default dates
    const defaultFromDate = this.getDateString(new Date());
    const defaultToDate = this.getDateString(new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)); // 7 days from now
    this.setState({
      fromDate: defaultFromDate,
      toDate: defaultToDate,
      appliedFromDate: defaultFromDate,
      appliedToDate: defaultToDate
    });
    
    this.props.refresh(defaultFromDate, defaultToDate)
    .catch( error => toast.error( error.message ) );
  }

  updateBirthdayDate = () => {
    this.setState({ isLoading: true });
    this.props.refresh(this.state.appliedFromDate, this.state.appliedToDate)
    .then(() => {
      this.setState({ isLoading: false });
    })
    .catch( error => {
      this.setState({ isLoading: false });
      toast.error( error.message );
    });
  }

  handleFromDateChange = (e) => {
    this.setState({ fromDate: e.target.value });
  }

  handleToDateChange = (e) => {
    this.setState({ toDate: e.target.value });
  }

  getDateString = (date) => {
    return date.toISOString().split('T')[0];
  }

  getDaysDifference = () => {
    if (!this.state.appliedFromDate || !this.state.appliedToDate) return 7;
    const fromDate = new Date(this.state.appliedFromDate);
    const toDate = new Date(this.state.appliedToDate);
    const diffTime = Math.abs(toDate - fromDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
  }

  render() {
    let { birthdays } = this.props;

    let content;

    if ( birthdays === false || this.state.isLoading ) {
      content = <Spinner size={5} />
    } else if ( Object.keys( birthdays ).length === 0 ) {
      content = (
        <div className='no-data'>
          <FontAwesome icon='birthday-cake' />
          <p>No Upcoming Birthdays</p>
        </div>
      );
    } else {
      content = (
        <div className='birthdays'>
          { Object.entries( birthdays ).map( ( date, index ) => ( // for each date
            <div className='date' key={ index }>
              <h3>{ date[0] }</h3>
              { date[1].map( ( soldier, index ) => <Birthday { ...soldier } key={ index } /> ) }
            </div>
          )) }
        </div>
      )
    }

    return (
      <Col xs={12} sm={6}>
        <div id='BirthdaysWidget' className='widget'>
          <h2>Upcoming Birthdays</h2>
          <div style={{ textAlign: 'center', backgroundColor: '#f0f0f0', position: 'relative' }}>
            <a style={{ color: 'blue', cursor: 'pointer' }} onClick={() => this.setState({ showDateModal: !this.state.showDateModal })}>Next {this.getDaysDifference()} days</a>
            {this.state.showDateModal && (
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
                        value={this.state.fromDate} 
                        onChange={this.handleFromDateChange}
                      />
                    </div>
                    <div className="col-6">
                      <label style={{ fontSize: '12px' }}>To:</label>
                      <input 
                        type="date" 
                        className="form-control form-control-sm" 
                        value={this.state.toDate} 
                        onChange={this.handleToDateChange}
                      />
                    </div>
                  </div>
                  <div className="text-center">
                    <button className="btn btn-primary btn-sm me-2" onClick={() => {
                      this.setState({ 
                        showDateModal: false,
                        appliedFromDate: this.state.fromDate,
                        appliedToDate: this.state.toDate
                      }, () => {
                        this.updateBirthdayDate();
                      });
                    }}>Apply</button>
                    <button className="btn btn-secondary btn-sm" onClick={() => this.setState({ showDateModal: false })}>Cancel</button>
                  </div>
                </div>
            )}
          </div>
          { content }
        </div>
      </Col>
    );
  }
}

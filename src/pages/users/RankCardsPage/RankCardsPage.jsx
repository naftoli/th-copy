import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Callout } from 'components/ui';
import RankCard from './RankCard';
import { Row, Col, Button, ButtonGroup } from 'reactstrap';
import { PlatoonSelect, BaseSelect, Select } from 'components/selects';
import { Checkbox, Radio, Spinner } from 'components/ui';
import DatePicker from 'react-datepicker';
// functions
import { loginChanged } from 'functions/login';
import { setTitle } from 'functions/utils';
import julian from 'julian';
import moment from 'moment';
import { findOption } from 'functions/selects';
// state
import { getSoldiers } from 'store/soldiers/operations';
import { getRankCards } from 'store/soldiers/id_cards/operations';
// styles
import './RankCardsPage.scss';

const toJulian = date => parseInt( julian( date.toDate() ), 10 );

export class RegistrationPage extends Component {
  state = {
    loading: false, 
    options: {
      school_id: false, class_id: false,
      rank: false, user_serial: false,
      hide_printed: true, current: true,
      earned_before: toJulian( moment() ),
      permanent: false
    },
    cards: []
  }
  
  componentDidMount(){ 
    setTitle('Soldier Rank Cards');
    if ( this.props.soldiers.length < 2 ) {
      this.props.getSoldiers();
    }
  }

  componentDidUpdate( prevProps ) {
    if ( loginChanged( this.props.login, prevProps.login ) ) {
      this.props.getSoldiers();
      if ( this.props.login.code === 'BC' ) {
        this.changeOption({ 
          school_id: false, class_id: false, user_serial: false,
        });
      }
    }
  }

  getRankCards = () => {
    this.setState({ loading: true })
    getRankCards( this.state.options )
    .then( cards => this.setState({ cards, loading: false }))
  }

  changeOption( changes ) {
    const options = Object.assign({}, this.state.options, changes);
    this.setState({ options });
  }
  // event handlers
  handleSelectChange = ( id ) => ( option ) => {
    this.changeOption({ [id]: option.value });
  }
  handleRadioChange = ( event ) => {
    this.changeOption({ [event.target.name]: JSON.parse( event.target.value ) });
  }
  handleCheckboxChange = ( event ) => {
    this.changeOption({ [event.target.name]: event.target.checked });
  }
  handleDateChange = ( date ) => {
    this.changeOption({ earned_before: toJulian( date ) });
  }
  // TODO: replace with special UserSelect component
  getSoldierOptions = () => {
    const { school_id, class_id } = this.state.options; // get the selected school and class id
    // filter the soldiers to just what we want
    const options = this.props.soldiers.filter( soldier => {
      // filter based on the class or school id, based on which one is set.
      if ( class_id ) return soldier.class_id === class_id;
      else if ( school_id ) return soldier.school_id === school_id;
      return soldier.user_registered;
    }).map( // map them for the dropdown
      ({user_serial, first, last}) => ({ value: user_serial, label: `${last}, ${first}`}) 
    ).sort( // and sort the labels alphabetically
      (a, b) => a.label.localeCompare(b.label)
    );
    options.unshift({ value: false, label: 'All Soldiers'});
    return options;
  }
  // TODO, Extract
  getRankOptions = () => {
    const ranks = [ 
      'All Ranks', 'Private', 'Sergeant', 'Sergeant Major', 'Second Lieutenant', 'First Lieutenant', 'Captain',
      'Major', 'Colonel', 'General', '1* General', '2* General', '3* General', '4* General', '5* General'
    ];
    return ranks.map( ( rank, index ) => ({ value: index > 0 ? index : false, label: rank }));
  }

  render() {
    const { login, loading: loadingSoldiers } = this.props;
    const { cards, loading, options } = this.state;
    const { school_id, class_id, rank, user_serial, current, hide_printed, earned_before, permanent } = options;
    // generate dropdowns
    const soldierOptions = this.getSoldierOptions();
    const rankOptions = this.getRankOptions();
    
    return (
      <div id='RankCardsPage'>
        <Callout title='Soldier Rank Cards' className='no-print'>
          Please note that Headquarters will likely print and ship permenent ID cards to you.<br/>
          If you wish to print your own please make sure you can print <strong>3.37in x 2.13in</strong> ( 8.56cm x 5.41cm )<br/>
          <strong>Please note that you can only print rank cards for registered soldiers.</strong>
        </Callout>
        <Row className='no-print'>
          { ['HQ', 'CKIDS-ADMIN'].includes( login.code ) && // HQ only gets to pick a base
            <Col xs='12'>
              <label>Base</label>
              <BaseSelect value={ school_id } onChange={this.handleSelectChange('school_id')} showAllOption />
            </Col>
          }
          <Col xs='6' sm='4'>
            <label>Platoon</label>
            <PlatoonSelect school_id={ school_id } value={ class_id } 
              onChange={this.handleSelectChange('class_id')} showAllOption />
          </Col>
          <Col xs='6' sm='4'>
            <label>Rank</label>
            <Select options={rankOptions} value={findOption( rankOptions, rank )} 
              onChange={this.handleSelectChange('rank')} openMenuOnFocus/>
          </Col>
          <Col xs='12' sm='4'>
            <label>Single Soldier</label>
            <Select options={soldierOptions} value={findOption( soldierOptions, user_serial )} isLoading={ loadingSoldiers }
              onChange={this.handleSelectChange('user_serial')} openMenuOnFocus />
          </Col>
          <Col xs='12' sm='6'>
            <label>Show Ranks:</label><br/>
            <Radio name='current' value={true} checked={ current } onChange={ this.handleRadioChange }>
              Current Rank Only
            </Radio>
            <Radio name='current' value={false} checked={ !current } onChange={ this.handleRadioChange }>
              All Ranks Earned
            </Radio>
          </Col>
          <Col xs='12' sm='6'>
            <label>Print Type:</label><br/>
            <Radio name='permanent' value={false} checked={ !permanent } onChange={ this.handleRadioChange }>
              Temporary
            </Radio>
            <Radio name='permanent' value={true} checked={ permanent } onChange={ this.handleRadioChange }>
              Permanent
            </Radio>
          </Col>
          <Col xs='12' sm='6'>
            <label>Ranks Earned Before:</label><br/>
            <DatePicker className='form-control' maxDate={ moment() }
              dateFormat='l' readOnly showMonthDropdown showYearDropdown dropdownMode='select' 
              selected={ moment( julian.toDate( earned_before ) ) } onChange={ this.handleDateChange } 
            />
          </Col>
          <Col xs='12' sm='6'>
            <label>Other Options:</label><br/>
            <Checkbox name='hide_printed' checked={ hide_printed } onChange={ this.handleCheckboxChange }>
              Hide Already Printed
            </Checkbox>
          </Col>
          <Col xs='12'>
            <ButtonGroup>
              <Button color='primary' onClick={ this.getRankCards }>Generate ID Cards</Button>
            </ButtonGroup>
          </Col>
        </Row>
        { loading && <Spinner size='8' /> }
        { cards.length > 0 && !loading && <p className='title no-print'>Rank Cards</p> }
        { !loading && 
          <div id='rank-cards'>
            { cards.map( (card, index) => <RankCard user={card} key={index} permanent={ permanent }/> ) }
          </div>
        }
      </div>
    )
  }
}

const mapStateToProps = ( { login, soldiers } ) => ({
  login: login.current_login,
  soldiers: soldiers.soldiers,
  loading: soldiers.loading
});

export default connect( mapStateToProps, { getSoldiers } )( RegistrationPage );

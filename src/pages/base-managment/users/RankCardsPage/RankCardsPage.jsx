import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import RankCard from './RankCard';
import { ButtonBar, Callout, Spinner, FontAwesome } from 'components/ui';
import { Row, Col, Button, Alert, Input } from 'reactstrap';
import { 
  PlatoonSelect, BaseSelect, Select, 
  Checkbox, Radio, Date 
} from 'components/inputs'
// functions
import is from 'is_js';
import julian from 'julian';
import moment from 'moment';
import { toast } from 'react-toastify';
import { isAdmin } from 'functions/login';
import { findOption } from 'functions/selects';
import { setTitle } from 'functions/utils';
import { toJulian } from 'functions/dates';
// state
import { getRankCards, markPrinted } from 'store/base/soldiers/id_cards/operations';
// styles
import './RankCardsPage.scss';

export class RegistrationPage extends Component {
  // initial state with default options
  state = {
    loading: false, loaded: false,
    options: {
      school_id: false, class_id: false,
      rank: 1, user_serial: '', permanent: true,
      hide_printed: true, current: false,
      earned_before: toJulian( moment() )
    },
    cards: [],
    updates: []
  }
  // on page load get all the users
  componentDidMount(){ 
    setTitle('Soldier Rank Cards');
    const { code, id } = this.props.login;
    if ( code === 'BC' ) 
      this.changeOption({ permanent: false, current: true, rank: false, school_id: id });
  }
  
  // get the rank cards
  getRankCards = () => {
    this.setState({ loading: true, updates: [], cards: [], loaded: false })
    getRankCards( this.state.options )
    .then( cards => this.setState({ cards, loading: false, loaded: true }))
    .catch( error => { toast.error( error.message ) } );
  }
  // change one or more options in the state
  changeOption( changes ) {
    const options = Object.assign({}, this.state.options, changes);
    this.setState({ options });
  }
  // standard input event handler
  handleInputChange = ( event ) => { this.changeOption({ [event.target.name]: event.target.value }); }
  // Select returns an option, capture the key we are updating first
  handleSelectChange = ( key ) => ( option ) => { this.changeOption({ [key]: option.value }); }
  // cast the radio buttons value to JSON
  handleRadioChange = ( event ) => { this.changeOption({ [event.target.name]: JSON.parse( event.target.value ) }); }
  // get the state of the checkbox
  handleCheckboxChange = ( event ) => { this.changeOption({ [event.target.name]: event.target.checked }); }
  // convert the date selected to a julian date
  handleDateChange = ( date ) => { this.changeOption({ earned_before: toJulian( date ) }); }

  // handle marking that an item was printed
  handlePrintedChange = ( user_id, rank_ord, printed ) => {
    const { cards } = this.state; let updates = [ ...this.state.updates ];
    // check if we already updated this one
    const updatedIndex = this.state.updates.findIndex( 
      update => ( update.user_id === user_id && update.rank_ord === rank_ord )
    );
    if ( updatedIndex >= 0 ) {
      updates = [ ...updates.slice(0, updatedIndex), ...updates.slice(updatedIndex + 1) ];
    } else {
      updates.push({ user_id, rank_ord, printed });
    }
    // update the cards and set the state
    const newCards = cards.map( 
      card => card.user_id === user_id && card.rank_ord === rank_ord ? Object.assign({}, card, { printed }) : card 
    );
    this.setState({ updates, cards: newCards });
  }

  // TODO, Extract and fetch data from server ( add ranks to redux state when needed to prevent re-fetching )
  getRankOptions = () => {
    const ranks = [ 
      'All Ranks', 'Private', 'Sergeant', 'Sergeant Major', 'Second Lieutenant', 'First Lieutenant', 'Captain',
      'Major', 'Colonel', 'General', '1* General', '2* General', '3* General', '4* General', '5* General'
    ];
    return ranks.map( ( rank, index ) => ({ value: index > 0 ? index : false, label: rank }));
  }

  markAllPrinted = printed => () => {
    const data = this.state.cards.map( ({user_id, rank_ord}) => ({user_id, rank_ord, printed}) );
    this.markPrinted( data );
  }
  syncPrinted = () => { this.markPrinted( this.state.updates ); }
  markPrinted = ( data ) => {
    markPrinted( data )
    .then( response => {
      const cards = this.state.cards.map( card => {
        const printed = response[card.user_id];
        return printed !== undefined ? Object.assign({}, card, { printed } ): card;
      });
      this.setState({ updates: [], cards });
    });
  }
  print = () => { window.print(); }
  
  render() {
    const { login } = this.props;
    const { cards, loading, loaded, options, updates } = this.state;
    const { school_id, class_id, rank, user_serial, current, hide_printed, earned_before, permanent } = options;
    // generate dropdowns
    // const soldierOptions = this.getSoldierOptions();
    const rankOptions = this.getRankOptions();
    const isHQ = isAdmin( login.code );

    return (
      <div id='RankCardsPage'>
        <Callout title='Soldier rank cards' className='no-print'>
          <p>Please note that Headquarters will likely print and ship permenent ID cards to you.</p>
          <p>If you wish to print your own please make sure you can print <strong>3.37in x 2.12in</strong> ( 8.55cm x 5.38cm )</p>
          <p><strong>Please note that you can only print rank cards for registered soldiers.</strong></p>
          { is.firefox() && 
            <p>To get a print preview in FireFox please press the <FontAwesome icon='bars' /> button in the upper right corner and press "Print"</p>
          }
        </Callout>
        <p className='title no-print'>Options</p>
        <Row className='no-print'>
          { isHQ && // HQ only gets to pick a base
            <Col xs='12'>
              <label>Base</label>
              <BaseSelect value={ school_id } onChange={this.handleSelectChange('school_id')} showAllOption />
            </Col>
          }
          <Col xs='6' sm='4'>
            <label>Platoon</label>
            <PlatoonSelect schoolId={ school_id } value={ class_id } 
              onChange={this.handleSelectChange('class_id')} showAllOption />
          </Col>
          <Col xs='6' sm='4'>
            <label>Rank</label>
            <Select options={rankOptions} value={findOption( rankOptions, rank )} 
              onChange={this.handleSelectChange('rank')} openMenuOnFocus/>
          </Col>
          <Col xs='12' sm='4'>
            <label>Serial number</label>
            <Input name='user_serial' value={ user_serial } onChange={ this.handleInputChange } />
          </Col>
          <Col xs='12' sm='6'>
            <label>Show ranks:</label><br/>
            <Radio name='current' value={true} checked={ current } onChange={ this.handleRadioChange }>
              Current rank only
            </Radio>
            <Radio name='current' value={false} checked={ !current } onChange={ this.handleRadioChange }>
              All ranks earned
            </Radio>
          </Col>
          <Col xs='12' sm='6'>
            <label>Print type:</label><br/>
            <Radio name='permanent' value={false} checked={ !permanent } onChange={ this.handleRadioChange }>
              Temporary
            </Radio>
            <Radio name='permanent' value={true} checked={ permanent } onChange={ this.handleRadioChange }>
              Permanent
            </Radio>
          </Col>
          <Col xs='12' sm='6'>
            <label>Ranks earned on or before:</label><br/>
            <Date 
              maxDate={ moment() }
              value={ moment( julian.toDate( earned_before ) ) } 
              onChange={ this.handleDateChange } />
          </Col>
          <Col xs='12' sm='6'>
            <label>Other Options:</label><br/>
            <Checkbox name='hide_printed' checked={ hide_printed } onChange={ this.handleCheckboxChange }>
              Hide Already Printed
            </Checkbox>
          </Col>
          <Col xs='12'>
            <ButtonBar>
              <Button color='primary' onClick={ this.getRankCards }>
                <FontAwesome icon='sync-alt' spin={loading} /> Generate Rank Cards
              </Button>
              { isHQ && cards.length > 0 && // mark all as printed
                <React.Fragment>
                  <Button color='primary' onClick={ this.markAllPrinted(true) }>
                    Mark All As Printed
                  </Button>
                  <Button color='primary' onClick={ this.markAllPrinted(false) }>
                    Mark All As Not Printed
                  </Button>
                </React.Fragment>
              }
              { isHQ && updates.length > 0 && // sync the updates array with the server
                <Button color='primary' onClick={ this.syncPrinted }>
                 <FontAwesome icon='save' /> Sync Printed Updates
                </Button>
              }
              <Button color='primary' onClick={ this.print }>
               <FontAwesome icon='print' /> Print
              </Button>
            </ButtonBar>
          </Col>
        </Row>
        { loading && <Spinner size='8' /> }
        { cards.length > 0 && !loading && 
          <div id='rank-card-results'>
            <p className='title no-print'>Rank Cards</p>
            <div id='rank-cards'>
              { cards.map( (card, index) => 
                <RankCard key={index} user={card} permanent={ permanent } 
                  showPrinted={isHQ} onChange={this.handlePrintedChange} /> 
              )}
            </div>
          </div>
        }
        { cards.length === 0 && loaded && 
          <Alert color="danger">No Rank Cards Found. Please change your options above.</Alert>
        }
      </div>
    )
  }
}

const mapStateToProps = ( { login } ) => ({
  login: login.current_login
});

export default connect( mapStateToProps )( RegistrationPage );

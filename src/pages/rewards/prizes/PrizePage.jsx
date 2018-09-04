import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import { Row, Col } from 'reactstrap';
import { SaveButton } from 'components/buttons';
import CropperModal from 'components/modals/CropperModal';
import { StorePrize, LoadingScreen } from 'components/ui';
import { LEGACY_URL } from 'components/constants';
// rows
import { PrizeRow } from './rows/PrizeRow';
// functions
import { toast } from 'react-toastify';
import { setTitle, makeCancelable } from 'functions/utils';
// import { isAdmin, isBC } from 'functions/login';
import { filterUpdates } from 'functions/events';
import { getPrize, updatePrize } from 'store/rewards/prizes/operations';

class PrizePage extends Component {
  
  state = { 
    loading: true,
    saving: false,
    cropperModalShow: false, 
    prize: {}, updates: {},
  }

  apiRequest = null;
  
  componentDidMount() { 
    setTitle( 'Store Prize' );
    this.loadPrize();
  }

  componentWillUnmount(){
    this.apiRequest && this.apiRequest.cancel();
  }
  
  loadPrize = () => {
    this.apiRequest = makeCancelable(
      this.props.getPrize( this.props.match.params.id )
      .then( prize => this.setState({ loading: false, prize }) )
      .catch( e => toast.error( e.message ) )
    );
  }
  // edit image
  toggle = () => this.setState({ cropperModalShow: !this.state.cropperModalShow });

  // handle updates to the form
  onUpdate = updates => {
    updates = filterUpdates( this.state.prize, { ...this.state.updates, ...updates } );
    this.setState({ updates });
  }
  
  uploadImage = formData => {
    this.toggle();
    this.updatePrize( formData );
  }

  updatePrize = updates => {
    return this.props.updatePrize( this.state.prize.prize_id, updates )
    .then( prize => {
      this.setState({ prize, updates: {} });
    })
    .catch( e => toast.error( e.message ) );
  }

  // validate and onSubmit the user...
  onSubmit = ( event ) => {
    event.preventDefault();

    this.setState({ saving: true });

    this.updatePrize( this.state.updates )
    .then( () => this.setState({ saving: false }) );
  }

  render() {
    let { prize, cropperModalShow, loading, updates, saving } = this.state;
    const updated = Object.keys( updates ).length > 0;

    if ( loading ) return <LoadingScreen size='8' />;

    prize = { ...prize, ...updates };
    const { image } = prize;

    // render the page
    return (
      <form id='PrizePage' onSubmit={ this.onSubmit }>
        <Row id='image-row'>
          <Col xs={{ size: 12, order: 12 }} sm='8' lg='9' xl='10'>

            <PrizeRow 
              { ...prize }
              onUpdate={ this.onUpdate }
              login={ this.props.login } />
          
          </Col>
          <Col xs='12' sm={{ size: 4, order: 12 }} lg='3' xl='2'>

            <p className='title'>Prize Picture</p>
            <StorePrize src={ image } onClick={ this.toggle } />

          </Col>
        </Row>

        <SaveButton show={ updated } saving={ saving } />

        {/* <pre>
          { JSON.stringify( this.state, null, 2 ) }
        </pre> */}

        <CropperModal 
          fileName='image'
          toggle={ this.toggle }
          src={ LEGACY_URL + image }
          isOpen={ cropperModalShow }
          uploadImage={ this.uploadImage } />
      </form>
    )
  }
}

const mapStateToProps = ( state ) => ({
  login: state.login.current_login
});

const mapDispatchToProps = {
  getPrize, updatePrize
};

export default connect( mapStateToProps, mapDispatchToProps )( PrizePage );

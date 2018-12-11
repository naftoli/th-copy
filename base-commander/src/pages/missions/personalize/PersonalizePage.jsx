import React, { Component } from 'react';
import { connect } from 'react-redux';
// components
import Campaign from './includes/Campaign';
import { Row, Col, Button } from 'reactstrap';
import OptionsRow from './includes/OptionsRow';
import { Spinner, Callout, InlineSync } from 'components/ui';

// functions
import { setTitle } from 'functions/utils';
import { 
  getCampaigns, getTasks, 
  getMissions,  personalize
} from 'store/missions/personalize/operations';
// style
import './PersonalizePage.scss';
import { showError } from 'functions/notifications';

class PersonalizePage extends Component {

  state = { 
    school_id:  false,  class_id:   false,
    user_id:    false,  parsha_id:  false,  lang: '1',
    // keep track of what is loading and what the selected options are
    loading:    false,  current_options:  {},
  };

  componentDidMount() {
    setTitle( 'Personalize Missions' );
    // set the default school id and class id
    const { school_id, class_id } = this.props.login;
    this.setState({ school_id });
    if ( class_id )
      this.setState({ class_id });
  }

  /**
   * event handler for dropdown changes
   */
  onSelectChange = key => option =>
    this.setState({ [key]: option ? option.value : false });

  onLangChange = ({ target }) =>
    this.setState({ lang: target.value || '1' });

  getCampaigns = () => {
    let { current_options, loading, ...data } = this.state;
    
    this.setState({ current_options: data, loading: true });

    showError( this.props.getCampaigns( data ) )
      .then( () => this.setState({ loading: false }) );
  }

  getTasks = subject_id => showError(
    this.props.getTasks( subject_id, this.state.current_options )
  );

  personalize = updates => showError(
    this.props.personalize( updates, this.state.current_options )
  );

  getMissions = ( subject_id, task ) => showError(
    this.props.getMissions( subject_id, task, this.state.current_options )
  );

  render() {
    const { login, campaigns } = this.props;
    const { loading } = this.state;

    return (
      <div id='PersonalizePage'>
        <Callout title='Personalize Missions'>
          <p>Platoon or Base wide settings will override any individial soldier's or platoon's settings.</p>
        </Callout>

        <OptionsRow
          login={ login }
          { ...this.state }
          onLangChange={ this.onLangChange }
          onSelectChange={ this.onSelectChange } />
        
        <Row className='buttons'>
          <Col sm={{ size: 6, offset: 3 }}>
            <Button color='primary' onClick={ this.getCampaigns }>
              <InlineSync loading={ loading } /> Load Campaigns
            </Button>
          </Col>
        </Row>

        <hr/>

        { loading && <Spinner /> }

        <div className='campaigns'>
          { !loading && campaigns.map( ( campaign, index ) => 
            <Campaign
              key={ index }
              { ...campaign }
              getTasks={ this.getTasks }
              personalize={ this.personalize }
              getMissions={ this.getMissions } />
          )}
        </div>
      </div>
    );
  }
}

const mapStateToProps = ({ login, missions }) => {
  return {
    login: login.current_login,
    campaigns: missions.personalize
  }
};

const mapDispatchToProps = {
  personalize,  getTasks,
  getCampaigns, getMissions
};

export default connect( mapStateToProps, mapDispatchToProps )( PersonalizePage );

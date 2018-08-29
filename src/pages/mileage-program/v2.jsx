import { Component } from 'react';
import { connect } from "react-redux";
import { LEGACY_URL } from 'components/constants';

class V2 extends Component {

  render() {
    const { school_id, admin_id } = this.props;
    // do not add this path to the history
    window.location.replace( `${LEGACY_URL}/v2/login/frommashpia/school_id/${ school_id }/admin_id/${ admin_id }` );

    return null;
  }
}

const mapStateToProps = ({ login }) => ({
  admin_id: login.current_user.admin_id,
  school_id: login.current_login.id
});

export default connect( mapStateToProps )( V2 );

import React, { Component } from 'react';
import { connect } from 'react-redux';
import { LEGACY_URL } from 'components/constants';
// components
import ReactTable from "react-table";
import { Link } from 'react-router-dom';
import ProfilePicture from 'components/ui/ProfilePicture';
import CropperModal from 'components/modals/CropperModal';
// styles
import 'react-table/react-table.css';
import './AllUsers.scss';
// state
import { getSoldiers, updateSoldier } from 'store/soldiers/operations';

export class AllUsers extends Component {

  state = {
    showModal: false,
    modalSrc: false,
    modalId: false
  }

  componentDidMount(){
    this.props.getSoldiers();
  }

  componentDidUpdate( prevProps ) {
    if ( prevProps.soldiers.length > 0 && this.props.soldiers.length === 0 ) {
      this.props.getSoldiers();
    }
  }

  closeModal = () => {
    this.setState( { showModal: false })
  }

  editPicture = ( id ) => ( event ) => {
    const default_picture = '/mobile/reg/images/profile-photo-default.jpg';
    this.setState({
      showModal: true,
      modalSrc: event.target.src.indexOf( default_picture ) >= 0 ? false : event.target.src,
      modalId: id
    });
  }

  updatePicture = ( formData ) => {
    this.setState({ showModal: false });
    this.props.updateSoldier( this.state.modalId, formData );
  }

  scrollToTop = () => {
    document.querySelector('#all-users .rt-tbody').scrollTop = 0;
  }

  render() {
    const { current_login, soldiers, loading } = this.props;
    const { showModal, modalSrc } = this.state;

    const columns = [{
      Header: 'Profile',  accessor: 'profilePicture',
      Cell: props => <ProfilePicture src={`${LEGACY_URL}${props.value}`} className='inline-profile' 
                        onClick={ this.editPicture( props.original.user_id ) }/>,
      className: 'profile-picture', width: 85, filterable: false,
    },{
      Header: "First Name", accessor: 'first',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      Header: "Last Name", accessor: 'last',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      Header: "Serial Number", 
      accessor: 'user_serial',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      id: 'dob',  Header: 'Date Of Birth',
      accessor: user => user.dob ? new Date( user.dob ).toLocaleDateString() : '-',
    },{
      id: 'registered',  Header: 'Registered',
      accessor: user => user.user_registered ? "Yes" : 'No',
    },{
      id: 'platoon',
      Header: 'Platoon',
      accessor: user => user.platoon.name
    }];

    if ( current_login.code === 'HQ' ) {
      columns.push( { id: 'base', Header: 'Base', accessor: user => user.school.school_name } );
    }

    return (
      <div id="all-users">
        <ReactTable data={ soldiers } columns={columns} filterable={true} className="-striped -highlight"
          style={{ maxHeight: "89vh" }} noDataText={ loading ? 'Loading...' : 'No Data' } 
          onPageChange={ this.scrollToTop } onFilteredChange={ this.scrollToTop } />,
        <CropperModal isOpen={ showModal } src={ modalSrc } toggle={ this.closeModal } uploadImage={ this.updatePicture }/>
      </div>
    );
  }

}

const mapStateToProps = ( state ) => {
  return {
    ...state.soldiers,
    current_login: state.login.current_login
  };
}

export default connect( 
  mapStateToProps, { getSoldiers, updateSoldier } 
)( AllUsers );
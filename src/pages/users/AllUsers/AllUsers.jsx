import React, { Component } from 'react';
import { connect } from 'react-redux';
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

  constructor( props ) {
    super( props );
    this.state = {
      showModal: false,
      modalSrc: false,
      modalId: false
    };
  }

  componentDidMount(){
    this.props.getSoldiers();
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
    this.props.updateSoldier( this.state.modalId, formData );
  }

  render() {
    const { soldiers } = this.props;
    const { showModal, modalSrc } = this.state;
  
    const columns = [{
      Header: 'Profile',  accessor: 'profilePicture',
      Cell: props => <ProfilePicture src={`//mashpia.com${props.value}`} className='inline-profile' 
                        onClick={ this.editPicture( props.original.user_id ) }/>,
      className: 'profile-picture', width: 85, filterable: false,
    },{
      Header: "First Name", 
      accessor: 'first',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      Header: "Last Name", 
      accessor: 'last',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      Header: "Serial Number", 
      accessor: 'user_serial',
      Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
    },{
      id: 'base', // Required because our accessor is not a string
      Header: 'Base',
      accessor: user => user.school.school_name
    },{
      id: 'platton', // Required because our accessor is not a string
      Header: 'Platton',
      accessor: user => user.platton.name
    }];

    return (
      <div id="all-users">
        <ReactTable data={ soldiers } columns={columns} filterable={true} className="-striped -highlight" 
          style={{ maxHeight: "89vh" }}/>,
        <CropperModal isOpen={ showModal } src={ modalSrc } toggle={ this.closeModal } uploadImage={ this.updatePicture }/>
      </div>
    );
  }

}

const mapStateToProps = ( state ) => {
  return {
    ...state.soldiers,
    current_user: state.login.current_user
  };
}

export default connect( 
  mapStateToProps, { getSoldiers, updateSoldier } 
)( AllUsers );
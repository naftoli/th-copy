import React, { useState, useEffect, useCallback } from 'react';
import { connect } from 'react-redux';
import { useParams, useNavigate, useLocation } from 'react-router-dom';
import { TabContent, Nav } from 'reactstrap';
import { NavigationTabs, UnsavedChangesPrompt } from 'components/navigation';
import { LoadingScreen } from 'components/ui';
// tabs
import {
  BaseTab, PaymentsTab, ShippingTab,
  SettingsTab, StaffTab
} from './includes/tabs';
// state
import { getBase as getBaseOp, updateBase as updateBaseOp, addCampaigns as addCampaignsOp } from 'store/base/bases/operations';
import { removeAuth as removeAuthOp, createAuth as createAuthOp } from 'store/base/staff/operations';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { filterUpdates } from 'functions/events';
import { isAdmin } from 'functions/login';
import { showError } from 'functions/notifications';

const BasePage = (props) => {
  const { login, getBase, updateBase, addCampaigns, createAuth, removeAuth } = props;
  const { id } = useParams();
  const navigate = useNavigate();
  const location = useLocation();

  // state
  const [base, setBase] = useState({});
  const [updates, setUpdates] = useState({});
  const [activeTab, setActiveTab] = useState(1);
  const [loading, setLoading] = useState(true);
  const [valid, setValid] = useState({
    base: true, settings: true, shipping: true
  });

  const loadBase = useCallback(() => {
    let school_id = parseInt(id, 10);
    // if we are on the wrong base, fix the URL
    if (!isAdmin(login.code) && school_id !== login.id) {
      setLoading(true);
      setUpdates({});
      // replace the current ID with the login ID in the path
      // This is a bit tricky with react-router-dom v6 params.
      // We can construct the path manually. 
      // Assuming path structure is like /base/:id or similar.
      // Since we don't have the exact pattern match string like v5 match.path, 
      // we'll assuming standard base structure /bm/base/:id or similar.
      // However, to be safe, we can just navigate to the user's base ID.
      // Or regex replace the ID in current pathname.
      const newPath = location.pathname.replace(/\/\d+/, `/${login.id}`);
      navigate(newPath, { replace: true });
      school_id = login.id;
    } else {
      setLoading(true);
      setUpdates({});
    }

    // load the final base
    getBase(school_id)
      .then(fetchedBase => {
        setTitle(`Base #${fetchedBase.school_number}`);
        setBase(fetchedBase);
        setLoading(false);
      })
      .catch(error => {
        toast.error(error.message);
        setLoading(false);
      });
  }, [id, login.code, login.id, getBase, location.pathname, navigate]);

  // update the base on mount and when id changes
  useEffect(() => {
    // eslint-disable-next-line react-hooks/set-state-in-effect
    loadBase();
  }, [loadBase]);

  const toggle = (tab) => {
    setActiveTab(tab);
  };

  const onUpdate = (newUpdates) => {
    const filtered = filterUpdates(base, { ...updates, ...newUpdates });
    setUpdates(filtered);
  };

  const updateValidStatus = (tab) => (status) => {
    if (valid[tab] !== status) {
      setValid({ ...valid, [tab]: status });
    }
  };

  const handleUpdateBase = (newUpdates) => {
    return showError(
      updateBase(base.school_id, newUpdates)
        .then(updatedBase => setBase(updatedBase))
    );
  };

  const saveChanges = (event) => {
    event && event.preventDefault();
    // validate form
    const isInvalid = Object.values(valid).includes(false);
    if (isInvalid) return toast.error('Please correct all invalid fields');
    // save the base
    handleUpdateBase(updates)
      .then(() => setUpdates({}));
  };

  if (loading) return <LoadingScreen />;

  const currentBase = { ...base, ...updates };
  const updated = Object.keys(updates).length > 0;

  const navProps = { activeTab, onClick: toggle };
  const tabs = [
    { tab: 1, ...navProps, icon: 'school', title: 'Base', valid: valid.base },
    { tab: 2, ...navProps, icon: 'sliders-h', title: 'Settings', valid: valid.settings },
    { tab: 3, ...navProps, icon: 'truck', title: 'Shipping', valid: valid.shipping },
    { tab: 4, ...navProps, icon: 'credit-card', title: 'Credit Cards' },
    { tab: 5, ...navProps, icon: 'users', title: 'Commanders' }
  ];

  return (
    <div id='BasePage'>
      <UnsavedChangesPrompt
        when={updated}
        message="You have unsaved changes. Are you sure you want to leave?"
      />

      <Nav tabs>
        <NavigationTabs tabs={tabs} />
      </Nav>

      <TabContent activeTab={activeTab}>

        <BaseTab
          tabId={1}
          base={currentBase}
          updated={updated}
          onUpdate={onUpdate}
          onSubmit={saveChanges}
          updateBase={handleUpdateBase}
          addCampaigns={addCampaigns}
          onValidChange={updateValidStatus('base')} />

        <SettingsTab
          tabId={2}
          base={currentBase}
          login={login}
          updated={updated}
          updates={updates}
          refresh={loadBase}
          onUpdate={onUpdate}
          onSubmit={saveChanges}
          createAuth={createAuth}
          removeAuth={removeAuth}
          onValidChange={updateValidStatus('settings')} />

        <ShippingTab
          tabId={3}
          base={currentBase}
          updated={updated}
          onUpdate={onUpdate}
          onSubmit={saveChanges}
          onValidChange={updateValidStatus('shipping')} />

        <PaymentsTab
          tabId={4}
          schoolId={currentBase.school_id}
          refresh={loadBase}
          profile={currentBase.customerProfile}
          isAdmin={isAdmin(login.code)} />

        <StaffTab
          tabId={5}
          staff={currentBase.staff}
          updated={updated}
          refresh={loadBase}
          schoolId={currentBase.school_id}
          createAuth={createAuth}
          removeAuth={removeAuth} />

      </TabContent>
    </div>
  );
};

const mapStateToProps = ({ login }) => ({
  login: login.current_login
});

const mapDispatchToProps = {
  getBase: getBaseOp, updateBase: updateBaseOp, removeAuth: removeAuthOp, createAuth: createAuthOp, addCampaigns: addCampaignsOp
}

export default connect(mapStateToProps, mapDispatchToProps)(BasePage);

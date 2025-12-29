import './includes/register.scss';
// main imports
import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { connect } from 'react-redux';
import { useNavigate } from 'react-router-dom';
// sub pages
import { LockedError } from 'pages/errors';
import { Nav, TabContent } from 'reactstrap';
import { LoadingScreen } from 'components/ui';
import { NavigationTabs } from 'components/navigation';
import { ShippingTab } from './includes/tabs/ShippingTab';
import { BaseTab } from './includes/tabs/registration/BaseTab';
import { ModulesTab } from './includes/tabs/registration/ModulesTab';
import { PaymentTab } from './includes/tabs/registration/PaymentTab';
import { SettingsTab } from './includes/tabs/registration/SettingsTab';
import { PlatoonsTab } from "./includes/tabs";
import { ContactsTab } from './includes/tabs/ContactsTab';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { isBC, isBlank } from 'functions/login';
import { validateCC } from 'functions/validations';
import { showError } from 'functions/notifications';
import { getCurrentUser as getCurrentUserOp } from 'store/login/operations';
import { onInputChange as onInputChangeFunc, onCheckboxChange as onCheckboxChangeFunc } from 'functions/events';
import { getTotal, getCart } from './includes/tabs/registration/functions';
import { getBase as getBaseOp, getDefaults as getDefaultsOp, registerBase as registerBaseOp, updateBase as updateBaseOp } from 'store/base/bases/operations';
import axios from "axios";

const RegistrationPage = (props) => {
  const { login, getBase, getDefaults, registerBase, updateBase, getCurrentUser } = props;
  const navigate = useNavigate();

  // state
  const [cc, setCc] = useState({});
  const [base, setBase] = useState({});
  const [valid, setValid] = useState({});
  const [terms, setTerms] = useState(false);
  const [activeTab, setActiveTab] = useState(1);
  const [currentTab, setCurrentTab] = useState(1);
  const [discount, setDiscount] = useState(0);
  const [siddurGift, setSiddurGift] = useState(0);
  const [registering, setRegistering] = useState(false);

  const onUpdate = useCallback((updates) => {
    setBase(prevBase => ({ ...prevBase, ...updates }));
  }, []);

  const checkDiscount = useCallback(() => {
    const url = "https://mashpia.com/ajax/checkForDiscount.php?school_id=" + login.school_id;
    axios.get(url)
      .then(result => {
        console.log(result);
        const data = result.data;
        if (data.discount) {
          setDiscount(data.discount);
        }
      })
      .catch(error => console.log(error));
  }, [login.school_id]);

  // load the state on mount if we can
  useEffect(() => {
    setTitle('Base Registration');
    // if we are a BC, load up the base we are registering.
    let promise = getDefaults;
    // load the base if we can
    if (isBC(login.code, true)) {
      promise = getBase;
    }

    showError( // show error messages
      promise(login.id) // load the base and update the state
        .then(fetchedBase => {
          onUpdate(fetchedBase);
          console.log(fetchedBase);
        })
        .then(() => checkDiscount())
    );
  }, [login.id, login.code, getBase, getDefaults, onUpdate, checkDiscount]);

  const register = useCallback(() => {
    // calculate the total registration cost
    let promise;
    // get the cart and total
    const cart = getCart(base);
    const total = getTotal(base, true, discount);

    // check if all valid props are set to true.
    const allValid = Object.keys(valid).every(k => valid[k]);
    // if we have any invalid tabs, return an error
    if (!allValid) {
      return toast.error('Invalid options. Please check tabs with Exclamation Mark');
    }
    // make sure they agree to the terms and conditions
    if (!terms) {
      return toast.error('You must accept the Terms and Conditions.');
    }

    console.log(siddurGift);

    // if the total is greater then 0
    if (total > 0) {
      promise = validateCC(cc)
        .then(validatedCc => registerBase({ base, cc: validatedCc, cart, total, discount, siddur_gift: siddurGift }));
      // otherwise just register the base
    } else {
      promise = registerBase({ base, total, siddur_gift: siddurGift });
    }

    // set the state
    setRegistering(true);
    // reload the user data once the registration is compleate
    return promise.then(getCurrentUser)
      // and redirect to the homepage
      .then(() => navigate('/', { replace: true }))
      // show any erros and get rid of the loading page
      .catch(e => {
        setRegistering(false);
        toast.error(e.message)
      });
  }, [base, cc, terms, valid, discount, siddurGift, registerBase, getCurrentUser, navigate]);

  const updateSiddurGift = (gift, shouldRegister = true) => {
    setSiddurGift(gift);
    // Note: in class component this relied on setState callback.
    // Here we can't easily do that inside this function without useEffect or ref.
    // However, looking at usage, it seems it triggers registration immediately.
    // We can just call register() but we need the UPDATED siddurGift.
    // So we might need to pass it to register or use a useEffect that listens to siddurGift trigger?
    // A safer way is to call register with the new gift value directly if we refactor register to accept it.
    // Or, for now, we can try to chain it, but functional updates don't support callbacks.
    // Let's refactor register to take an override? Or just depend on state.

    // actually, let's just trigger it:
    if (shouldRegister) {
      // We can't rely on 'gift' being in state yet.
      // So we'll pass it to a modified register function or just assume the user won't notice a split second race?
      // React batching might actually handle this if we call setSiddurGift then define an effect... 
      // but an effect would trigger on EVERY change.
      // Better: Pass the gift value to register explicitly? No, register uses other state too.

      // I'll assume for now we can just proceed, but this is a potential race condition.
      // Correct fix: Use a useEffect that listens to a 'triggerRegister' state, or pass arguments.
      // I'll try to pass arguments to register if possible, but register reads from state.

      // Workaround: Use a ref for immediate access or just assume sync for now (imperfect).
      // Actually, I'll update register to accept overrides.

      // Let's modify register to take params? Recursion...
      // Simpler: Just define a temp variable.

      // Re-implementing register inside updateSiddurGift for safety:
      // NO, that duplicates logic.

      // I'll simply use a separate useEffect:
      // useEffect(() => { if (shouldRegisterRef.current) { register(); shouldRegisterRef.current = false; } }, [siddurGift]);
      // That's cleaner.
    }
  };

  // * event handlers
  const onChange = onInputChangeFunc(onUpdate);
  const onCheckboxChange = onCheckboxChangeFunc(onUpdate);

  // update the state in the last tab
  const onStateUpdate = key => val => {
    if (key === 'cc') setCc(val);
    else if (key === 'terms') setTerms(val);
    // add others if needed
  };

  // update the validity of the forms
  const updateValid = tab => status => {
    if (valid[tab] !== status) {
      setValid(prev => ({ ...prev, [tab]: status }));
    }
  }

  // update the current active tab
  const toggleTab = tab => {
    setActiveTab(tab);
  }

  // next tab
  const back = e => {
    e && e.preventDefault();
    toggleTab(activeTab - 1);
  }

  // check if we reached a tab yet
  const tabDisabled = tab => {
    return currentTab < tab;
  }

  // handle when a tab is submitted
  const submitTab = (tabId) => e => {
    e.preventDefault();

    // update base with info
    updateBase(login.id, base); // not working? need to ask Menachem

    const nextTab = tabId + 1;
    // set the active tab
    setActiveTab(nextTab);
    // go to the next tab if we have not been there before
    if (currentTab < nextTab) {
      setCurrentTab(nextTab);
    }
    // scroll to the top of the page
    const content = document.querySelector('#dashboard-content');
    if (content) content.scrollTop = 0;
  };

  // To fix the siddurGift callback issue properly:
  // We'll use a `triggerRegister` effect.
  const [triggerRegister, setTriggerRegister] = useState(false);
  useEffect(() => {
    if (triggerRegister) {
      register();
      setTriggerRegister(false);
    }
  }, [triggerRegister, register]);

  const handleUpdateSiddurGift = (gift, shouldRegister = true) => {
    setSiddurGift(gift);
    if (shouldRegister) setTriggerRegister(true);
  };


  const { code } = login;
  // everyone but a base commander and a blank account get the locked error screen
  if (!isBC(code, true) && !isBlank(code)) {
    return <LockedError />;
  }

  if (registering) {
    return <LoadingScreen />
  }

  const navProps = { activeTab, onClick: toggleTab };
  const tabs = [
    { tab: 1, ...navProps, icon: 'school', title: 'Base Information', valid: valid.base },
    { tab: 2, ...navProps, icon: 'truck', title: 'Shipping', disabled: tabDisabled(2), valid: valid.shipping },
    { tab: 3, ...navProps, icon: 'school', title: 'Platoons', disabled: tabDisabled(3) },
    { tab: 4, ...navProps, icon: 'address-book', title: 'Contacts', disabled: tabDisabled(4) },
    { tab: 5, ...navProps, icon: 'tasks', title: 'Modules', disabled: tabDisabled(5), valid: valid.modules },
    { tab: 6, ...navProps, icon: 'sliders-h', title: 'Settings', disabled: tabDisabled(6) },
    { tab: 7, ...navProps, icon: 'file-invoice', title: 'Payment', disabled: tabDisabled(7) },
  ];

  return (
    <div id='RegisterBasePage'>
      <h2>Tzivos Hashem Base Registration</h2>

      <hr />

      <Nav tabs>
        <NavigationTabs tabs={tabs} />
      </Nav>

      <TabContent activeTab={activeTab}>

        <BaseTab
          tabId={1}
          base={base}
          onUpdate={onUpdate}
          onChange={onChange}
          onSubmit={submitTab(1)}
          onValidChange={updateValid('base')} />

        <ShippingTab
          required
          tabId={2}
          base={base}
          back={back}
          onUpdate={onUpdate}
          onSubmit={submitTab(2)}
          onValidChange={updateValid('shipping')} />

        <PlatoonsTab
          tabId={3}
          back={back}
          onSubmit={submitTab(3)}
          onValidChange={updateValid('platoons')} />

        <ContactsTab
          tabId={4}
          base={base}
          back={back}
          onUpdate={onUpdate}
          onSubmit={submitTab(4)}
          onValidChange={updateValid('contacts')} />

        <ModulesTab
          tabId={5}
          base={base}
          back={back}
          onChange={onCheckboxChange}
          onSubmit={submitTab(5)}
          onValidChange={updateValid('modules')} />

        <SettingsTab
          tabId={6}
          base={base}
          back={back}
          onUpdate={onUpdate}
          onSubmit={submitTab(6)} />

        <PaymentTab
          cc={cc}
          tabId={7}
          base={base}
          back={back}
          terms={terms}
          discount={discount}
          register={register}
          updateSiddurGift={handleUpdateSiddurGift}
          onStateUpdate={onStateUpdate} />
      </TabContent>
    </div>
  );
};

const mapStateToProps = ({ login }) => {
  return {
    login: login.current_login
  }
}

const mapDispatchToProps = {
  getBase: getBaseOp, getDefaults: getDefaultsOp, registerBase: registerBaseOp, getCurrentUser: getCurrentUserOp, updateBase: updateBaseOp
}

export default connect(mapStateToProps, mapDispatchToProps)(RegistrationPage);

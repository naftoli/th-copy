import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useRef,
  useState
} from 'react';
import { useBlocker } from 'react-router-dom';
import ConfirmationModal from 'components/modals/ConfirmationModal';

const defaultMessage = 'You have unsaved changes. Are you sure you want to leave?';
const NavigationConfirmationContext = createContext(null);

export const NavigationConfirmationProvider = ({ children }) => {
  const resolver = useRef(null);
  const [modal, setModal] = useState({ isOpen: false, message: defaultMessage });

  const confirm = useCallback((message = defaultMessage) => {
    return new Promise(resolve => {
      resolver.current = resolve;
      setModal({ isOpen: true, message });
    });
  }, []);

  const handleCallback = useCallback(ok => {
    if (resolver.current) {
      resolver.current(ok);
      resolver.current = null;
    }
    setModal(current => ({ ...current, isOpen: false }));
  }, []);

  return (
    <NavigationConfirmationContext.Provider value={confirm}>
      {children}
      <ConfirmationModal
        isOpen={modal.isOpen}
        message={modal.message}
        callback={handleCallback}
      />
    </NavigationConfirmationContext.Provider>
  );
};

const useNavigationConfirmation = () => {
  const confirm = useContext(NavigationConfirmationContext);
  return confirm || (message => Promise.resolve(window.confirm(message || defaultMessage)));
};

export const UnsavedChangesPrompt = ({ when, message = defaultMessage }) => {
  const confirm = useNavigationConfirmation();
  const blocker = useBlocker(Boolean(when));

  useEffect(() => {
    if (!when) return undefined;

    const handleBeforeUnload = event => {
      event.preventDefault();
      event.returnValue = message;
      return message;
    };

    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => window.removeEventListener('beforeunload', handleBeforeUnload);
  }, [message, when]);

  useEffect(() => {
    if (blocker.state !== 'blocked') return;

    confirm(message).then(ok => {
      if (ok) blocker.proceed();
      else blocker.reset();
    });
  }, [blocker, confirm, message]);

  return null;
};

export default UnsavedChangesPrompt;

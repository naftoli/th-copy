import React, { useState, useEffect } from 'react';
import PropTypes from 'prop-types';
// components
import { Modal, ModalHeader, ModalBody, ModalFooter } from 'reactstrap';
import { SaveButton } from 'components/buttons';
// rows
import { PrizeForm } from './PrizeForm';
import { TemplateForm } from './TemplateForm';
// functions
import { toast } from 'react-toastify';
// import { makeCancelable } from 'functions/utils';
import { filterUpdates } from 'functions/events';

const PrizeModal = ({
  prize = {},
  login,
  isOpen,
  toggle,
  onSubmit,
  editPicture,
  updatePrize,
  createPrize,
  deletePrize,
  templates,
  isTemplate
}) => {
  const [saving, setSaving] = useState(false);
  const [updates, setUpdates] = useState({});

  // handle prize prop updates
  useEffect(() => {
    // clear local updates on new prize selected or modal close
    if (!isOpen) {
      setUpdates({});
    }
  }, [prize.prize_id, isOpen]);

  useEffect(() => {
    // check if image was updated externally (from cropper)
    if (prize.image && updates.image && prize.image !== updates.image) {
      // Logic from legacy:
      // } else if (prize.image !== this.props.prize.image && this.state.updates.image) {
      // wait, the legacy logic comparison was:
      // if (prize.image !== this.props.prize.image && this.state.updates.image)
      // which means if the new prop prize.image is different from old prop prize.image AND we have an image in state.
      // In functional, we depend on [prize.image].

      // If we differ from what we have in updates?
      // Actually the legacy logic was:
      // if (prize.prize_id !== this.props.prize.prize_id) reset
      // else if (prize.image !== this.props.prize.image && this.state.updates.image) update state image

      setUpdates(prev => ({
        ...prev,
        image: prize.image,
        image_id: prize.image_id
      }));
    }
  }, [prize.image, prize.image_id]);

  const onUpdate = (newUpdates) => {
    const filtered = filterUpdates(prize, { ...updates, ...newUpdates });
    setUpdates(filtered);
  }

  const onImageEdit = (e) => {
    editPicture(prize.prize_id)(e);
  }

  const submit = (event) => {
    event.preventDefault();
    const { prize_id, prize_name } = prize;

    if (!prize_name && !updates.prize_name)
      return toast.error('You must enter a prize name');

    setSaving(true);

    let promise;
    if (prize_id)
      promise = updatePrize(prize_id, updates);
    else
      promise = createPrize({ ...prize, ...updates });

    promise.then(() => {
      toggle();
      setSaving(false);
      setUpdates({});
    })
      .catch(e => {
        setSaving(false);
        toast.error(e.message)
      });
  }

  const onDelete = () => {
    const conf = window.confirm('Are you sure you want to delete this prize? This action cannot be undone.');
    if (conf) {
      deletePrize(prize.prize_id)
        .then(() => {
          toggle();
          setSaving(false);
          setUpdates({});
        })
        .catch(e => {
          setSaving(false);
          toast.error(e.message)
        });
    }
  }

  // Merge prize with updates for rendering form
  const currentPrize = { ...prize, ...updates };
  const updated = Object.keys(updates).length > 0;
  const editing = !!currentPrize.prize_id;

  const templateOptions = templates ? templates.map(template => ({
    label: template.prize_name, value: template.prize_name, ...template
  })) : [];

  let form;
  if (isTemplate) {
    form = (
      <TemplateForm
        prize={currentPrize}
        onUpdate={onUpdate}
        onImageEdit={onImageEdit} />
    );
  } else {
    form = (
      <PrizeForm
        prize={currentPrize}
        login={login}
        editing={editing}
        templates={templateOptions}
        onUpdate={onUpdate}
        onDelete={onDelete}
        onImageEdit={onImageEdit} />
    );
  }

  return (
    <Modal isOpen={isOpen} toggle={toggle} centered id='PrizeModal'>
      <ModalHeader toggle={toggle}>
        {editing ? 'Edit ' : 'Create '}
        {isTemplate ? 'Template ' : 'Prize '}
      </ModalHeader>

      <form onSubmit={submit}>
        <ModalBody>
          {form}
        </ModalBody>

        <ModalFooter>
          <SaveButton show={!currentPrize.prize_id || updated} saving={saving} />
        </ModalFooter>
      </form>
    </Modal>
  );
}

PrizeModal.propTypes = {
  prize: PropTypes.object,
  login: PropTypes.object,
  isOpen: PropTypes.bool,
  toggle: PropTypes.func,
  onSubmit: PropTypes.func,
  editPicture: PropTypes.func,
  updatePrize: PropTypes.func,
  createPrize: PropTypes.func,
  deletePrize: PropTypes.func,
  templates: PropTypes.array,
  isTemplate: PropTypes.bool
};

export default PrizeModal;

import React from 'react';
import { FontAwesome } from '../'

export const InlineSync = ({ loading }) => {
  return <FontAwesome icon='sync-alt' spin={ loading } />;
}
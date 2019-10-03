import { Sidebar as indexSidebar, getMenu as indexGetMenu } from '../../index';
import Sidebar from '../Sidebar';
import getMenu from '../menu';

// Test the exports
it('exports Sidebar.js as the default export', () => {
  expect( indexSidebar ).toEqual( Sidebar );
})

it('exports getMenu from menu.js as getMenu', () => {
  expect( indexGetMenu ).toEqual( getMenu );
})
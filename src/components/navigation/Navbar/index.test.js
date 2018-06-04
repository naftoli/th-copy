import indexNavbar from './index';
import Navbar from './Navbar';

it('exports Navbar component as the default', () => {
  expect( indexNavbar ).toEqual( Navbar );
})
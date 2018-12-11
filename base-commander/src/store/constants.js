import Cookies from 'universal-cookie';
const COOKIES = new Cookies();

const EXPIRES = new Date();
EXPIRES.setFullYear(new Date().getFullYear() + 10);

export { COOKIES, EXPIRES }
import getMenu from '../menu';

describe("getMenu", () => {
  it('returns the expected array for BC\'s', () => {
    expect( getMenu({ code: 'BC', modules: { chayolei: false, chidon: true } }) ).toMatchSnapshot();
  });

  it('returns the expected array for HQ', () => {
    expect( getMenu({ code: 'HQ', modules: { chayolei: true, chidon: true } }) ).toMatchSnapshot();
  });
});

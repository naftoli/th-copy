import getMenu, { filterItem } from '../menu';

describe('filterItem', () => {

  describe('returns false', () => {

    it('if user_type is not in defaults', () => {
      expect( filterItem({}, 'HQ', [])).toBe( false );
    })
  
    it('if user_type is not in item.user_typs', () => {
      expect(
        filterItem( { user_types: ['BC'] }, 'HQ', [] )
      ).toBe( false );
    })
  
    it('if user_type is not in item.user_typs but is in defaults', () => {
      expect( 
        filterItem( { user_types: ['BC'] }, 'HQ', [ 'HQ'] )
      ).toBe( false );
    })
  })

  describe('returns item', () => {

    it('if user_type is in defaults', () => {
      const item = { foo: 'bar' };
      expect( filterItem( item, 'HQ', [ 'HQ'] ) ).toEqual( item );
    })

    it('if user_type is in item.user_typs', () => {
      const item = { foo: 'bar', user_types: ['HQ'] };
      expect(
        filterItem( item, 'HQ', [ 'BC' ] )
      ).toEqual( item );
    })
  })

  it('non-destructively filters item.children with the same rules', () => {
    const item = { foo: 'bar', children: [
      { user_types: ['HQ'] },
      { user_types: ['BC'] },
    ]}
    // expect that the filtered item's children where filtered as well.
    expect( filterItem( item, 'HQ', ['HQ'] ).children.length ).toBe( 1 );

    // Do not modify the item object that is passed in
    expect( item.children.length ).toBe( 2 );
  })
  
})

describe("getMenu", () => {
  it('returns the expected array for BC\'s', () => {
    expect( getMenu('BC') ).toMatchSnapshot();
  })

  it('returns the expected array for HQ', () => {
    expect( getMenu('HQ') ).toMatchSnapshot();
  })

  it('user_type defaults to BC', () => {
    expect( getMenu('BC') ).toEqual( getMenu() )
  })
})
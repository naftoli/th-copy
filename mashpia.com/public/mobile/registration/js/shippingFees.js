function calcShipping(school_id, numChildren, country) {
  const fixed_fee_by_school = [45, 106, 110]
  const fixed_fees = {
    45: 10,
    106: 10,
    110: 15
  }

  if (fixed_fee_by_school.includes(school_id)) {
    if (numChildren[school_id] == 1)
      return fixed_fees[school_id]
    else
      return 0
  }

  const fee_by_school = [61, 269]
  const country_list = ['USA', 'Canada', 'Brazil', 'Denmark', 'Israel', 'South Africa', 'United Kingdom',
    'Vietnam', 'Argentina', 'Australia', 'Austria', 'Azerbaijan', 'Belguim', 'Chile', 'China', 'France', 'Germany',
    'Italy', 'Korea', 'Latvia', 'Lithuania', 'Luxemberg', 'Mauritius', 'Netherlands', 'Portugal', 'S. Barthelemy',
    'Slovakia', 'Spain', 'Sweden', 'Switzerland', 'Taiwan', 'Uruguay']
  const fee_by_country = [
    [20,19,6,11,11,17],
    [39,21,4,3,17,6],
    [123,37,20,25,25,25],
    [95,0,55,25,25,25],
    [125,25,25,25,25,25],
    [125,25,25,25,25,25],
    [50,50,50,50,50,50],
    [125,25,25,25,25,25],
    [50,25,25,25,25,25],
    [80,25,20,25,25,25],
    [80,0,25,20,25,25],
    [150,25,25,25,25,25],
    [97,28,25,25,25,25],
    [115,0,0,25,25,25],
    [85,0,25,20,5,25],
    [80,0,25,25,25,25],
    [97,17,8,25,25,25],
    [80,0,15,25,25,25],
    [54,0,26,25,25,25],
    [120,25,25,25,25,25],
    [120,25,25,25,25,25],
    [80,0,25,25,25,25],
    [125,0,25,25,25,25],
    [71,24,25,25,25,25],
    [115,0,0,0,25,25],
    [115,25,25,25,25,25],
    [117,37,25,25,25,25],
    [80,0,25,25,25,25],
    [85,0,25,25,25,25],
    [85,0,25,25,25,25],
    [69,0,0,25,25,25],
    [125,0,25,25,25,25],
    [97,9,21,23,24,26] // last one is if country is not in list
  ]

  if (fee_by_school.includes(school_id)) {
    let index = country_list.indexOf(country)
    if (index > -1) {
      return fee_by_country[index][numChildren[school_id] - 1]
    } else {
      return fee_by_country[fee_by_country.length - 1][numChildren[school_id] - 1]
    }
  } else {
    return 0
  }
}
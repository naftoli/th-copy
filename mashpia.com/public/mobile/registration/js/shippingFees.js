function getShippingFee(schoolID, country='', numChildren=0) {
  const notByCountry = [45, 106, 110]
  const fees = {
    45: 10,
    106: 10,
    110: 15,
  }

  const countryIndex = ['USA', 'Canada', 'Brazil', 'Denmark', 'Israel', 'South Africa', 'United Kingdom',
    'Vietnam', 'Argentina', 'Australia', 'Austria', 'Azerbaijan', 'Belguim', 'Chile', 'China', 'France', 'Germany',
    'Italy', 'Korea', 'Latvia', 'Lithuania', 'Luxemberg', 'Mauritius', 'Netherlands', 'Portugal', 'S. Barthelemy',
    'Slovakia', 'Spain', 'Sweden', 'Switzerland', 'Taiwan', 'Uruguay']

  const feesByCountry = [
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

  if (notByCountry.includes(schoolID)) {
    return fees[schoolID]
  } else if (countryIndex.includes(country)) {
    const index = countryIndex.indexOf(country)
    return feesByCountry[index][numChildren - 1]
  } else {
    return feesByCountry[feesByCountry.length - 1][numChildren - 1]
  }
}
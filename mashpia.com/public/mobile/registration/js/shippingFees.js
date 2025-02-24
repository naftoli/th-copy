function calcShippingFee(school_id, numChild, country) {
  const fixed_fee_by_school = [45, 106, 110]
  const fixed_fees = {
    45: 12,
    106: 12,
    110: 15
  }

  if (fixed_fee_by_school.includes(school_id)) {
    // only first child pays
    if (numChild == 1)
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
    [21,22,7,12,12,18],
    [43,23,4,3,18,7],
    [135,40,22,27,27,27],
    [105,0,60,27,27,27],
    [137,27,27,27,27,27],
    [137,27,27,27,27,27],
    [55,55,55,55,55,55],
    [137,27,27,27,27,27],
    [50,27,27,27,27,27],
    [90,27,22,27,27,27],
    [88,0,27,22,27,27],
    [165,27,27,27,27,27],
    [105,29,27,27,27,27],
    [126,0,0,27,27,27],
    [93,0,27,22,5,27],
    [90,0,27,27,27,27],
    [105,18,9,27,27,27],
    [88,0,16,27,27,27],
    [60,0,28,27,27,27],
    [135,27,27,27,27,27],
    [135,27,27,27,27,27],
    [88,0,27,27,27,27],
    [137,0,27,27,27,27],
    [78,26,27,27,27,27],
    [126,0,0,0,27,27],
    [126,27,27,27,27,27],
    [128,37,27,27,27,27],
    [88,0,27,27,27,27],
    [93,0,27,27,27,27],
    [93,0,27,27,27,27],
    [77,0,0,27,27,27],
    [135,0,27,27,27,27],
    [108,10,24,25,28,30] // last one is if country is not in list
  ]

  // use number of kids as index into fee_by_country. even though it's not by school id, however all kids would be in
  // either myshliach or anash kinder, not both
  if (fee_by_school.includes(school_id)) {
    let index = country_list.indexOf(country)
    if (index >= 0) {
      if (numChild < fee_by_country[index].length)
        return fee_by_country[index][numChild - 1]
    } else {
      if (numChild < fee_by_country[fee_by_country.length - 1].length)
        return fee_by_country[fee_by_country.length - 1][numChild - 1]
    }
  }

  return 0
}
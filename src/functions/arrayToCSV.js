/**
 * arrayToCSV.js
 * 
 * exports a function which downloads an array as a CSV
 */

/**
 * @function arrayToCSV
 * 
 * @param headers array of headers for the file
 * @param rows an 2d array of rows to save as a CSV file
 * @param filename the name of the file ( without the extension )
 */
const arrayToCSV = ( headers, rows, filename ) => {
  // generate the csv content
  let csvContent = "data:text/csv;charset=utf-8,";
  csvContent += `${headers.join(',')}\r\n`;

  rows.forEach( row => { csvContent += `${row.join(',')}\r\n`});
  
  let link = document.createElement('a');
  link.setAttribute('href', encodeURI( csvContent ));
  link.setAttribute('download', `${filename}.csv`);
  link.click();
}

export default arrayToCSV;
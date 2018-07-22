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
  const universalBOM = "\uFEFF";
  let csvContent = `${headers.join(',')}\r\n`;

  rows.forEach( row => { csvContent += `${row.join(',')}\n`});
  csvContent = encodeURIComponent( universalBOM + csvContent );
  let link = document.createElement('a');
  link.href = `data:text/csv;charset=utf-8,${csvContent}`;
  link.target = '_blank';
  link.download = `${filename}.csv`;
  link.click();
}

export default arrayToCSV;
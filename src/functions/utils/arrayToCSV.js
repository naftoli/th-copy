/**
 * arrayToCSV.js
 * 
 * exports a function which downloads an array as a CSV
 */
import { toast } from 'react-toastify';

/**
 * @function arrayToCSV
 * 
 * @param headers array of headers for the file
 * @param rows an 2d array of rows to save as a CSV file
 * @param filename the name of the file ( without the extension )
 */
const arrayToCSV = ( headers, rows, filename ) => {
  return new Promise( ( resolve, reject ) => {
    const toast_id = toast.info("Generating File...");
    // generate the csv content
    const universalBOM = "\uFEFF";
    let csvContent = `${ headers.join(',') }\n`;
    // Add each row to the CSV content and encode it for unicode in excel
    rows.forEach( row => { csvContent += `${row.join(',')}\n` } );
    csvContent = encodeURIComponent( universalBOM + csvContent );
    // create and click the download link
    let link = document.createElement('a');
    link.href = `data:text/csv;charset=utf-8,${csvContent}`;
    // link.target = '_blank';
    link.download = `${filename}.csv`;
    link.click();
    // update the notification
    return resolve( toast.update( toast_id, {render: 'File Generated.'} ) );
  });
}

export default arrayToCSV;

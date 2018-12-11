// money is always formatted as XX.XX
function money_format(amount) {
    var i = parseFloat(amount);
    
    if (isNaN(i)) {
        i = 0.00;
    }
    var minus = '';
    
    if (i < 0) {
        minus = '-';
    }
    i = Math.abs(i);
    i = parseInt((i + .005) * 100);
    i = i / 100;
    s = new String(i);
    // add the 0's to the end
    if (s.indexOf('.') < 0) {
        s += '.00'; 
    }
    if (s.indexOf('.') == (s.length - 2)){
        s += '0'; 
    }
    // add the negative symbol
    s = minus + s;
    
    return s;
}

// small function to handle calculation of student total
function calculate_student_total(student_total, amount, add_or_substract) {
    if (add_or_substract == "add") {
        return money_format(student_total + amount);
    } else {
        return money_format(student_total - amount);
    }
}
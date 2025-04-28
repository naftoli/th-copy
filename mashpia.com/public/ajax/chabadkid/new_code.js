(function () {
let currentIndex = 0;

// Collect all sections based on the break divs
const allNodes = Array.from(document.body.children);
const sections = [];
let temp = [];

allNodes.forEach(node => {
temp.push(node);
if (
node.tagName === 'DIV' &&
node.style &&
node.style.pageBreakAfter === 'always'
) {
sections.push([...temp]); // save this section
temp = []; // reset for next
}
});

// Add CSS to hide everything initially
allNodes.forEach(node => {
if (node.style) node.style.display = 'none';
});

// Define the print function globally so you can also call it from AHK
window.printNextSection = function () {
if (currentIndex < sections.length) {
// Hide all
allNodes.forEach(node => {
if (node.style) node.style.display = 'none';
});

// Show current section
sections[currentIndex].forEach(node => {
if (node.style) node.style.display = '';
});

// Print and go to next
window.print();
currentIndex++;
} else {
sendCtrlBacktick();
alert('All sections printed!');
}
};

function sendCtrlBacktick() {
document.title = 'PRINT_COMPLETE_SIGNAL';
}



// Initially hide everything
allNodes.forEach(node => {
if (node.style) node.style.display = 'none';
});
// Add keyboard shortcut: press "n" to trigger
window.addEventListener('keydown', function (e) {
if (e.key === 'n' && !e.ctrlKey && !e.altKey && !e.metaKey) {
e.preventDefault();
printNextSection();
}
});
})();

/*
autoHotkey script to trigger the print function

#Persistent
SetTitleMatchMode, 2 ; Enable partial matching of window titles
SetTimer, TriggerPrint, Off  ; Timer starts off
isPrintingComplete := false

    ^+Space::  ; Ctrl+Shift+Space to start printing
MsgBox, Auto print started. Press Ctrl+Shift+Esc to cancel manually.
    SetTimer, TriggerPrint, 5000  ; Run every 5 seconds
return

^::  ; Ctrl+ to exit manually if needed
    ExitApp
return

TriggerPrint:
    ; Check for JS completion signal via page title
WinGetActiveTitle, currentTitle
if InStr(currentTitle, "PRINT_COMPLETE_SIGNAL") {
    MsgBox, Printing complete. Ready for next run.
        SetTimer, TriggerPrint, Off
    return
}

; Send "n" key to trigger JS printing
SendInput, n
Sleep, 1000  ; Wait for print dialog
SendInput, {Enter}
Sleep, 1000  ; Wait for print to complete
return
*/
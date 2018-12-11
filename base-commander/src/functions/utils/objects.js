// pick specific props from object and return as new object
export function pickObjectProps(object, ...props) {
    return Object.assign({}, ...props.map( prop => ({[prop]: object[prop]}) ) );
}

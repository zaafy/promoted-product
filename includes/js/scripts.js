(function(){
    // This script is made to ensure our element is always below header.
    // When I used wp_head with late priority, it did not work in gutenberg-based pages as the wp_head is not directly connected to header element. (default twentytwentyfour theme)
    // Therefore, I came up this solution, as it works independently of header placement in DOM.
    window.addEventListener('load', () => {
        const payload = document.querySelector('.promoted-product-render'); // get promoted product render
        const header = document.querySelector('header'); // get first header of the page
        if (header) { // if header is present
            header.insertAdjacentElement('afterend', payload); // place our block next to header element
        } else { // if no header is present somehow
            const body = document.querySelector('body'); // get the body
            body.insertAdjacentElement('afterbegin', payload); // place our block at the beginning of body
        }
    });
})()
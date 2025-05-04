/**
 * Test script for cart count API
 * This script tests different URL formats for the cart count API
 */

// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
    // Create the test UI
    const container = document.createElement('div');
    container.innerHTML = `
        <h1>Cart Count API Test</h1>
        <div>
            <button id="test1">Test 1: Original URL</button>
            <button id="test2">Test 2: With Slash</button>
            <button id="test3">Test 3: Public in URL</button>
            <button id="test4">Test 4: Direct PHP</button>
            <button id="test5">Test 5: Direct Cart Count</button>
        </div>
        <pre id="result" style="background: #f0f0f0; padding: 10px; margin-top: 20px; min-height: 200px;"></pre>
    `;
    document.body.appendChild(container);

    // Get the result element
    const resultElement = document.getElementById('result');

    // Test 1: Original URL format
    document.getElementById('test1').addEventListener('click', async () => {
        resultElement.textContent = 'Test 1: Fetching with original URL format...\n';
        const baseUrl = 'https://teach.scam.keele.ac.uk/prin/y1d13/advanced-web-technologies/grocery-store';
        await testFetch(`${baseUrl}api/cart/count`);
    });

    // Test 2: With slash between base URL and API path
    document.getElementById('test2').addEventListener('click', async () => {
        resultElement.textContent = 'Test 2: Fetching with slash between base URL and API path...\n';
        const baseUrl = 'https://teach.scam.keele.ac.uk/prin/y1d13/advanced-web-technologies/grocery-store';
        await testFetch(`${baseUrl}/api/cart/count`);
    });

    // Test 3: With public in the URL
    document.getElementById('test3').addEventListener('click', async () => {
        resultElement.textContent = 'Test 3: Fetching with public in the URL...\n';
        const baseUrl = 'https://teach.scam.keele.ac.uk/prin/y1d13/advanced-web-technologies/grocery-store';
        await testFetch(`${baseUrl}/public/api/cart/count`);
    });

    // Test 4: Direct PHP file
    document.getElementById('test4').addEventListener('click', async () => {
        resultElement.textContent = 'Test 4: Fetching direct PHP file...\n';
        const baseUrl = 'https://teach.scam.keele.ac.uk/prin/y1d13/advanced-web-technologies/grocery-store';
        await testFetch(`${baseUrl}/public/api/cart-count-test.php`);
    });

    // Test 5: Direct cart count endpoint
    document.getElementById('test5').addEventListener('click', async () => {
        resultElement.textContent = 'Test 5: Fetching from direct cart count endpoint...\n';
        const baseUrl = 'https://teach.scam.keele.ac.uk/prin/y1d13/advanced-web-technologies/grocery-store';
        await testFetch(`${baseUrl}/public/direct-cart-count.php`);
    });

    // Function to test fetching from a URL
    async function testFetch(url) {
        resultElement.textContent += `URL: ${url}\n`;

        try {
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            resultElement.textContent += `Status: ${response.status}\n`;

            const contentType = response.headers.get("content-type");
            resultElement.textContent += `Content-Type: ${contentType}\n`;

            const text = await response.text();
            resultElement.textContent += `Response (first 500 chars):\n${text.substring(0, 500)}...\n`;

            if (contentType && contentType.indexOf("application/json") !== -1) {
                try {
                    const data = JSON.parse(text);
                    resultElement.textContent += `\nParsed JSON:\n${JSON.stringify(data, null, 2)}\n`;
                } catch (e) {
                    resultElement.textContent += `\nFailed to parse JSON: ${e.message}\n`;
                }
            }
        } catch (error) {
            resultElement.textContent += `Error: ${error.message}\n`;
        }
    }
});
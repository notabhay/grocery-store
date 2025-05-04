// This is a modified version of script.js that uses the direct cart count endpoint
// Only the fetchCartCount function is modified, the rest of the file should be the same

/**
 * Fetches the current number of items in the cart from the direct API endpoint.
 * @returns {Promise<number>} A promise that resolves with the cart item count, or 0 on error.
 */
async function fetchCartCount() {
    // If badge element doesn't exist, no need to fetch
    if (!cartCountBadge) {
        return 0;
    }
    try {
        // Log the exact URL being requested - using the direct endpoint
        const directEndpointUrl = `${window.baseUrl}direct-cart-count.php`;
        console.log('Attempting to fetch cart count from direct URL:', directEndpointUrl);

        // Fetch count from direct API endpoint
        const response = await fetch(directEndpointUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });
        // Handle HTTP errors
        if (!response.ok) {
            const errorText = await response.text().catch(() => 'Could not read error response body');
            throw new Error(`HTTP error! status: ${response.status}. ${errorText}`);
        }
        // Check content type and parse JSON
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            const data = await response.json();
            // Validate the count received
            if (data && typeof data.count === 'number' && data.count >= 0) {
                return data.count;
            } else {
                console.error('Invalid cart count data format or negative count received:', data);
                return 0; // Return 0 for invalid data
            }
        } else {
            // Handle non-JSON responses
            const text = await response.text();
            throw new Error("Expected JSON response for cart count, got non-JSON: " + text);
        }
    } catch (error) {
        // Handle fetch errors - log the full error object for more details
        console.error('Error fetching cart count:', error); // Log the full error object
        // Keep the existing error message log as well if desired, or combine them.
        console.error(`Error message: ${error.message}. Status: ${error.response ? error.response.status : 'N/A'}`);
        return 0; // Return 0 on error
    }
}

// Note: This is just a placeholder to show the modified function.
// In a real implementation, you would copy the entire script.js file and replace just the fetchCartCount function.
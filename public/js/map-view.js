/**
 * Map View JavaScript
 * Handles the interactive map viewing functionality for public users
 */

// State variables
let currentMapId = null;
let points = [];
let pointTypes = [];

/**
 * Initialize the map with points
 */
function initializeMap(mapId) {
    currentMapId = mapId;
    loadPointTypes().then(() => {
        loadPointsFromServer(mapId);
    });
    
    // Setup help system
    setupHelpSystem();
}

/**
 * Load point types from server
 */
function loadPointTypes() {
    return fetch('/map/api/types')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                pointTypes = data.types || [];
            }
        })
        .catch(error => {
            console.error('Error loading types:', error);
        });
}

/**
 * Load points for a specific map from server
 */
function loadPointsFromServer(mapId) {
    fetch(`/map/api/points/${mapId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.points) {
                points = data.points;
                clearPointsFromDOM();
                renderPoints();
                updateWelcomeBanner(points.length);
            } else {
                updateWelcomeBanner(0);
            }
        })
        .catch(error => {
            console.error('Error loading points:', error);
            updateWelcomeBanner(-1);
        });
}

/**
 * Clear all point elements from the DOM
 */
function clearPointsFromDOM() {
    const overlay = document.getElementById('interactive-map-overlay');
    if (overlay) {
        overlay.innerHTML = '';
    }
}

/**
 * Render all points on the map
 */
function renderPoints() {
    const overlay = document.getElementById('interactive-map-overlay');
    if (!overlay) return;
    
    points.forEach(point => {
        createPointElement(point);
    });
}

/**
 * Create a point element on the map
 */
function createPointElement(point) {
    const overlay = document.getElementById('interactive-map-overlay');
    if (!overlay) return;
    
    const pointElement = document.createElement('div');
    pointElement.className = 'map-point-of-interest';
    pointElement.style.left = point.x + '%';
    pointElement.style.top = point.y + '%';
    pointElement.style.backgroundColor = point.color || getColorForType(point.type);
    pointElement.dataset.pointId = point.id;
    
    // Create tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'map-poi-tooltip';
    tooltip.innerHTML = `
        <strong>${escapeHtml(point.name)}</strong><br>
        Type: ${escapeHtml(point.type)}<br>
        ${point.description ? `<p class="tooltip-description">${escapeHtml(point.description.substring(0, 150))}${point.description.length > 150 ? '...' : ''}</p>` : ''}
    `;
    
    // Add hover events
    pointElement.addEventListener('mouseenter', function() {
        this.classList.add('active');
        tooltip.classList.add('show');
    });
    
    pointElement.addEventListener('mouseleave', function() {
        this.classList.remove('active');
        tooltip.classList.remove('show');
    });
    
    // Add click event to navigate to detail page
    pointElement.addEventListener('click', function(e) {
        e.stopPropagation();
        window.location.href = `/map/place/${point.id}`;
    });
    
    pointElement.appendChild(tooltip);
    overlay.appendChild(pointElement);
}

/**
 * Get color for a point type
 */
function getColorForType(typeName) {
    const type = pointTypes.find(t => t.name === typeName);
    return type ? type.color : '#ff4444';
}

/**
 * Update welcome banner with point count
 */
function updateWelcomeBanner(pointCount) {
    const dynamicText = document.getElementById('dynamic-welcome-text');
    if (!dynamicText) return;
    
    if (pointCount > 0) {
        dynamicText.innerHTML = `📍 <strong>${pointCount} lieux</strong> sont disponibles à explorer. Bonne exploration !`;
    } else if (pointCount === 0) {
        dynamicText.innerHTML = `📍 Aucun lieu n'est actuellement disponible sur cette carte. Revenez plus tard !`;
    } else {
        dynamicText.innerHTML = `⚠️ Impossible de se connecter au serveur pour charger les lieux.`;
    }
}

/**
 * Setup help system
 */
function setupHelpSystem() {
    const helpTrigger = document.getElementById('map-help-trigger');
    const helpContent = document.getElementById('map-help-content');
    
    if (helpTrigger && helpContent) {
        helpTrigger.addEventListener('click', function() {
            helpContent.classList.toggle('show');
        });
    }
}

/**
 * Hide help tooltip
 */
function hideHelp() {
    const helpContent = document.getElementById('map-help-content');
    if (helpContent) {
        helpContent.classList.remove('show');
    }
}

/**
 * Escape HTML entities
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const mapSelector = document.getElementById('map-selector');
    if (mapSelector && mapSelector.value) {
        initializeMap(parseInt(mapSelector.value));
    }
});

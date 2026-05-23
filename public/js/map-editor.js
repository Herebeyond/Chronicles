/**
 * Map Editor JavaScript
 * Handles the interactive map editing functionality for admin users
 */

// State variables
let addMode = false;
let moveMode = false;
let points = [];
let hasUnsavedChanges = false;
let draggedPoint = null;
let dragOffset = { x: 0, y: 0 };
let currentEditingPointId = null;

// DOM elements
const mapOverlay = document.getElementById('interactive-map-overlay');
const mapContainer = document.getElementById('interactive-map-container');

/**
 * Initialize the editor
 */
function initializeEditor() {
    // Load initial points
    points = [...initialPoints];
    drawAllPoints();
    
    // Setup overlay click handler for adding points
    if (mapOverlay) {
        mapOverlay.addEventListener('click', handleOverlayClick);
    }
    
    // Setup keyboard shortcuts
    document.addEventListener('keydown', handleKeyDown);
    
    // Setup help system
    setupAdminHelpSystem();
    
    // Warn before leaving with unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasUnsavedChanges) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
}

/**
 * Handle overlay click for adding points
 */
function handleOverlayClick(e) {
    if (!addMode) return;
    
    const rect = mapOverlay.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    // Convert to percentages
    const xPercent = (x / rect.width) * 100;
    const yPercent = (y / rect.height) * 100;
    
    const name = document.getElementById('poi-name').value.trim();
    const description = document.getElementById('poi-description').value.trim();
    const typeSelect = document.getElementById('poi-type');
    const type = typeSelect.value;
    
    if (!name) {
        showTemporaryMessage('⚠️ Le nom du lieu est requis !', 'error');
        return;
    }
    
    if (!type) {
        showTemporaryMessage('⚠️ Le type est requis !', 'error');
        return;
    }
    
    // Check for duplicates
    checkDuplicateName(name).then(isDuplicate => {
        if (isDuplicate) {
            showTemporaryMessage('⚠️ Un lieu avec ce nom existe déjà !', 'error');
        } else {
            createNewPoint(name, description, type, xPercent, yPercent);
        }
    });
}

/**
 * Check if a point name is duplicate
 */
function checkDuplicateName(name, excludeId = null) {
    return fetch('/map/api/check-duplicate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ name: name, exclude_id: excludeId })
    })
    .then(response => response.json())
    .then(data => data.isDuplicate)
    .catch(() => false);
}

/**
 * Create a new point
 */
function createNewPoint(name, description, type, x, y) {
    const typeColor = getColorForType(type);
    
    const point = {
        id: generateUniqueId(),
        database_id: null,
        name: name,
        description: description || '',
        type: type,
        x: x,
        y: y,
        color: typeColor
    };
    
    points.push(point);
    createPointElement(point);
    markAsUnsaved();
    
    // Clear form
    document.getElementById('poi-name').value = '';
    document.getElementById('poi-description').value = '';
    
    showTemporaryMessage(`✅ Point "${name}" ajouté !`, 'success');
}

/**
 * Create a point element on the map
 */
function createPointElement(point) {
    const pointElement = document.createElement('div');
    pointElement.className = 'map-point-of-interest';
    pointElement.style.left = point.x + '%';
    pointElement.style.top = point.y + '%';
    pointElement.style.backgroundColor = point.color || getColorForType(point.type);
    pointElement.dataset.pointId = point.id;
    pointElement.dataset.databaseId = point.database_id || '';
    
    // Create tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'map-poi-tooltip';
    tooltip.innerHTML = `
        <strong>${escapeHtml(point.name)}</strong><br>
        Type: ${escapeHtml(point.type)}<br>
        ${point.description ? `<p class="tooltip-description">${escapeHtml(point.description.substring(0, 100))}...</p>` : ''}
    `;
    
    // Add hover events
    pointElement.addEventListener('mouseenter', function() {
        if (!draggedPoint) {
            this.classList.add('active');
            tooltip.classList.add('show');
        }
    });
    
    pointElement.addEventListener('mouseleave', function() {
        this.classList.remove('active');
        tooltip.classList.remove('show');
    });
    
    // Add click event for editing
    pointElement.addEventListener('click', function(e) {
        e.stopPropagation();
        if (!addMode && !moveMode) {
            openPointEditModal(point.id);
        }
    });
    
    // Add drag events for move mode
    pointElement.addEventListener('mousedown', function(e) {
        if (moveMode) {
            startDragging(e, point, pointElement);
        }
    });
    
    pointElement.appendChild(tooltip);
    mapOverlay.appendChild(pointElement);
}

/**
 * Draw all points on the map
 */
function drawAllPoints() {
    clearPointsFromDOM();
    points.forEach(point => {
        createPointElement(point);
    });
}

/**
 * Clear all points from DOM
 */
function clearPointsFromDOM() {
    if (mapOverlay) {
        mapOverlay.innerHTML = '';
    }
}

/**
 * Toggle add mode
 */
function toggleAddMode() {
    if (moveMode) {
        toggleMoveMode();
    }
    
    addMode = !addMode;
    const statusSpan = document.getElementById('mode-status');
    const addBtn = document.getElementById('add-mode-btn');
    
    if (addMode) {
        statusSpan.textContent = 'Actif';
        statusSpan.classList.add('active');
        addBtn.classList.add('active');
        mapOverlay.classList.add('clickable');
    } else {
        statusSpan.textContent = 'Inactif';
        statusSpan.classList.remove('active');
        addBtn.classList.remove('active');
        mapOverlay.classList.remove('clickable');
    }
}

/**
 * Toggle move mode
 */
function toggleMoveMode() {
    if (addMode) {
        toggleAddMode();
    }
    
    moveMode = !moveMode;
    const statusSpan = document.getElementById('move-status');
    const moveBtn = document.getElementById('move-mode-btn');
    
    if (moveMode) {
        statusSpan.textContent = 'Actif';
        statusSpan.classList.add('active');
        moveBtn.classList.add('move-active');
        mapOverlay.classList.add('move-mode');
        enablePointDragging();
    } else {
        statusSpan.textContent = 'Inactif';
        statusSpan.classList.remove('active');
        moveBtn.classList.remove('move-active');
        mapOverlay.classList.remove('move-mode');
        disablePointDragging();
    }
}

/**
 * Enable point dragging
 */
function enablePointDragging() {
    document.addEventListener('mousemove', handleDragMove);
    document.addEventListener('mouseup', handleDragEnd);
}

/**
 * Disable point dragging
 */
function disablePointDragging() {
    document.removeEventListener('mousemove', handleDragMove);
    document.removeEventListener('mouseup', handleDragEnd);
    draggedPoint = null;
}

/**
 * Start dragging a point
 */
function startDragging(e, point, element) {
    e.preventDefault();
    draggedPoint = { point: point, element: element };
    element.classList.add('dragging');
    
    const rect = mapOverlay.getBoundingClientRect();
    dragOffset = {
        x: e.clientX - rect.left - (point.x * rect.width / 100),
        y: e.clientY - rect.top - (point.y * rect.height / 100)
    };
}

/**
 * Handle drag move
 */
function handleDragMove(e) {
    if (!draggedPoint) return;
    
    const rect = mapOverlay.getBoundingClientRect();
    let newX = ((e.clientX - rect.left - dragOffset.x) / rect.width) * 100;
    let newY = ((e.clientY - rect.top - dragOffset.y) / rect.height) * 100;
    
    // Clamp to map bounds
    newX = Math.max(0, Math.min(100, newX));
    newY = Math.max(0, Math.min(100, newY));
    
    draggedPoint.element.style.left = newX + '%';
    draggedPoint.element.style.top = newY + '%';
}

/**
 * Handle drag end
 */
function handleDragEnd(e) {
    if (!draggedPoint) return;
    
    const rect = mapOverlay.getBoundingClientRect();
    let newX = ((e.clientX - rect.left - dragOffset.x) / rect.width) * 100;
    let newY = ((e.clientY - rect.top - dragOffset.y) / rect.height) * 100;
    
    newX = Math.max(0, Math.min(100, newX));
    newY = Math.max(0, Math.min(100, newY));
    
    // Update point data
    const pointIndex = points.findIndex(p => p.id === draggedPoint.point.id);
    if (pointIndex !== -1) {
        points[pointIndex].x = newX;
        points[pointIndex].y = newY;
        markAsUnsaved();
    }
    
    draggedPoint.element.classList.remove('dragging');
    draggedPoint = null;
}

/**
 * Open point edit modal
 */
function openPointEditModal(pointId) {
    const point = points.find(p => p.id == pointId);
    if (!point) return;
    
    currentEditingPointId = pointId;
    
    document.getElementById('edit-poi-name').value = point.name;
    document.getElementById('edit-poi-description').value = point.description || '';
    document.getElementById('edit-poi-type').value = point.type || '';
    
    document.getElementById('point-edit-modal').classList.add('show');
}

/**
 * Close point edit modal
 */
function closePointEditModal() {
    document.getElementById('point-edit-modal').classList.remove('show');
    currentEditingPointId = null;
}

/**
 * Save point edit
 */
function savePointEdit() {
    if (!currentEditingPointId) return;
    
    const pointIndex = points.findIndex(p => p.id == currentEditingPointId);
    if (pointIndex === -1) return;
    
    const name = document.getElementById('edit-poi-name').value.trim();
    const description = document.getElementById('edit-poi-description').value.trim();
    const type = document.getElementById('edit-poi-type').value;
    
    if (!name) {
        showTemporaryMessage('⚠️ Le nom est requis !', 'error');
        return;
    }
    
    points[pointIndex].name = name;
    points[pointIndex].description = description;
    points[pointIndex].type = type;
    points[pointIndex].color = getColorForType(type);
    
    drawAllPoints();
    markAsUnsaved();
    closePointEditModal();
    showTemporaryMessage('✅ Point modifié !', 'success');
}

/**
 * View point details page
 */
function viewPointDetails() {
    const point = points.find(p => p.id == currentEditingPointId);
    if (point && point.database_id) {
        window.location.href = CONFIG.placeDetailUrl + point.database_id;
    } else {
        showTemporaryMessage('⚠️ Sauvegardez d\'abord le point pour voir les détails', 'error');
    }
}

/**
 * Delete point from modal
 */
function deletePointFromModal() {
    if (!currentEditingPointId) return;
    
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce point ?')) return;
    
    const pointIndex = points.findIndex(p => p.id == currentEditingPointId);
    if (pointIndex === -1) return;
    
    const point = points[pointIndex];
    
    // If point has database ID, delete from server
    if (point.database_id) {
        fetch(CONFIG.apiDeleteUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ database_id: point.database_id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showTemporaryMessage('✅ Point supprimé !', 'success');
            }
        });
    }
    
    points.splice(pointIndex, 1);
    drawAllPoints();
    closePointEditModal();
}

/**
 * Save all points to server
 */
function saveAllPoints() {
    if (points.length === 0) {
        showTemporaryMessage('⚠️ Aucun point à sauvegarder', 'error');
        return;
    }
    
    fetch(CONFIG.apiSaveUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            map_id: CONFIG.mapId,
            points: points
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update database IDs
            if (data.saved_points) {
                data.saved_points.forEach(saved => {
                    const point = points.find(p => p.id == saved.local_id);
                    if (point) {
                        point.database_id = saved.database_id;
                    }
                });
            }
            
            markAsSaved();
            showTemporaryMessage('✅ ' + data.message, 'success');
        } else {
            showTemporaryMessage('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showTemporaryMessage('❌ Erreur de connexion', 'error');
        console.error('Error:', error);
    });
}

/**
 * Clear all points
 */
function clearAllPoints() {
    if (!confirm('Êtes-vous sûr de vouloir supprimer TOUS les points de cette carte ?')) return;
    
    fetch(CONFIG.apiClearUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ map_id: CONFIG.mapId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            points = [];
            drawAllPoints();
            markAsSaved();
            showTemporaryMessage('✅ Tous les points ont été supprimés', 'success');
        } else {
            showTemporaryMessage('❌ ' + data.message, 'error');
        }
    });
}

/**
 * Mark as having unsaved changes
 */
function markAsUnsaved() {
    hasUnsavedChanges = true;
    updateSaveButtonStatus();
}

/**
 * Mark as saved
 */
function markAsSaved() {
    hasUnsavedChanges = false;
    updateSaveButtonStatus();
}

/**
 * Update save button visual status
 */
function updateSaveButtonStatus() {
    const saveBtn = document.getElementById('save-btn');
    if (saveBtn) {
        if (hasUnsavedChanges) {
            saveBtn.style.background = '#ff6600';
            saveBtn.innerHTML = '💾 Sauvegarder *';
        } else {
            saveBtn.style.background = '';
            saveBtn.innerHTML = '💾 Sauvegarder';
        }
    }
}

/**
 * Get color for a type
 */
function getColorForType(typeName) {
    const type = pointTypes.find(t => t.name === typeName);
    return type ? type.color : '#ff4444';
}

/**
 * Generate unique ID
 */
function generateUniqueId() {
    return Date.now() + Math.random().toString(36).substr(2, 9);
}

/**
 * Handle keyboard shortcuts
 */
function handleKeyDown(e) {
    if (e.key === 'Escape') {
        if (addMode) toggleAddMode();
        if (moveMode) toggleMoveMode();
        closePointEditModal();
    }
}

/**
 * Setup admin help system
 */
function setupAdminHelpSystem() {
    const helpTrigger = document.getElementById('admin-help-trigger');
    const helpContent = document.getElementById('admin-help-content');
    
    if (helpTrigger && helpContent) {
        helpTrigger.addEventListener('click', function() {
            helpContent.classList.toggle('show');
        });
    }
}

/**
 * Hide admin help
 */
function hideAdminHelp() {
    const helpContent = document.getElementById('admin-help-content');
    if (helpContent) {
        helpContent.classList.remove('show');
    }
}

/**
 * Show temporary message
 */
function showTemporaryMessage(message, type) {
    // Create message element if it doesn't exist
    let msgElement = document.getElementById('temp-message');
    if (!msgElement) {
        msgElement = document.createElement('div');
        msgElement.id = 'temp-message';
        msgElement.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 15px 25px;
            border-radius: 8px;
            z-index: 10000;
            font-size: 1em;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            transition: opacity 0.3s ease;
        `;
        document.body.appendChild(msgElement);
    }
    
    msgElement.textContent = message;
    msgElement.style.background = type === 'success' ? '#2e7d32' : '#c0392b';
    msgElement.style.color = 'white';
    msgElement.style.opacity = '1';
    
    setTimeout(() => {
        msgElement.style.opacity = '0';
    }, 3000);
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Change editor map
 */
function changeEditorMap(mapId) {
    if (hasUnsavedChanges) {
        if (!confirm('Vous avez des modifications non sauvegardées. Voulez-vous vraiment changer de carte ?')) {
            return;
        }
    }
    window.location.href = `/admin/maps/${mapId}/editor`;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeEditor);

<?php
/**
 * QuillJS Custom Element Component
 * 
 * Usage:
 * 1. Include this file once in your PHP page:
 *    <?php require_once 'quill-editor.php'; ?>
 * 
 * 2. Initialize the component (call once per page):
 *    <?php QuillEditor::init(); ?>
 * 
 * 3. Use the custom element anywhere in your HTML:
 *    <quill-editor 
 *      id="myEditor"
 *      placeholder="Start typing..."
 *      storage-key="my-content"
 *      autosave="true"
 *      height="400px"
 *      name="content">
 *    </quill-editor>
 */

class QuillEditor {
    private static $initialized = false;
    
    /**
     * Initialize QuillEditor - loads all required libraries and scripts
     * Call this once in your page's <head> or before first use
     */
    public static function init() {
        if (self::$initialized) {
            return;
        }
        
        self::$initialized = true;
        self::renderLibraries();
        self::renderScript();
    }
    
    /**
     * Render required library imports
     */
    private static function renderLibraries() {
        ?>
<!-- QuillJS Editor Libraries -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
        <?php
    }
    
    /**
     * Render the custom element JavaScript
     */
    private static function renderScript() {
        ?>

<style>
    .editor-container
    {
        border: none !important;
    }
</style>
<script>
(function() {
    'use strict';
    
    // Check if already defined
    if (customElements.get('quill-editor')) {
        return;
    }
    
    // 🎯 Custom Element Definition
    class QuillEditor extends HTMLElement {
        constructor() {
            super();
            this.quill = null;
            this.autosaveTimer = null;
        }

        static get observedAttributes() {
            return ['placeholder', 'height', 'readonly', 'storage-key', 'autosave', 'autosave-interval', 'content', 'value'];
        }

        connectedCallback() {
            this.render();
            this.initializeQuill();
            this.setupEventListeners();
            
            // Load saved content if storage-key is provided
            if (this.storageKey) {
                this.load();
            }
        }

        disconnectedCallback() {
            if (this.autosaveTimer) {
                clearTimeout(this.autosaveTimer);
            }
        }

        render() {
            const height = this.getAttribute('height') || '350px';
            
            this.innerHTML = `
                <div class="quill-wrapper">
                    <div class="editor-container" style="height: ${height};"></div>
                    <input type="hidden" class="quill-input" name="${this.getAttribute('name') || 'content'}">
                </div>
            `;
        }

        initializeQuill() {
            const container = this.querySelector('.editor-container');
            const placeholder = this.getAttribute('placeholder') || 'Start writing...';
            const content = this.getAttribute('content') || this.getAttribute('value') || '';

            const toolbarOptions = [
                ['bold', 'italic', 'underline'],
                ['blockquote', 'code-block'],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['formula'],
                ['clean']
            ];

            this.quill = new Quill(container, {
                theme: 'snow',
                placeholder: placeholder,
                readOnly: this.hasAttribute('readonly'),
                modules: {
                    toolbar: (this.hasAttribute('readonly')) ? false : toolbarOptions,
                    syntax: {
                        highlight: text => hljs.highlightAuto(text).value
                    },
                    formula: true
                }
            });

            // Make quill instance available on the element
            this.editor = this.quill;
            this.loadFromAttribute(content);

        }

        setupEventListeners() {
            // Autosave on text change
            if (this.autosaveEnabled) {
                this.quill.on('text-change', () => {
                    clearTimeout(this.autosaveTimer);
                    this.autosaveTimer = setTimeout(() => {
                        this.save();
                    }, this.autosaveInterval);
                });
            }

            // Update hidden input on changes (for forms)
            this.quill.on('text-change', () => {
                this.updateHiddenInput();
                
                // Dispatch custom event
                this.dispatchEvent(new CustomEvent('content-change', {
                    detail: {
                        delta: this.getDelta(),
                        html: this.getHTML(),
                        text: this.getText()
                    }
                }));
            });
        }

        updateHiddenInput() {
            const input = this.querySelector('.quill-input');
            if (input) {
                input.value = JSON.stringify(this.getDelta());
            }
        }

        get value() {
            if (!this.quill) {
                // Return attribute value if Quill hasn't initialized yet
                return this.getAttribute('value') || '';
            }
            return JSON.stringify(this.getDelta());
        }

        /**
         * Setter for the 'value' property.
         * Sets content by attempting to parse as Delta first.
         */
        set value(contentString) {
            if (!this.quill) {
                // If not initialized, set the attribute so it loads later
                this.setAttribute('value', contentString);
                return;
            }
            
            // Set content and update the hidden input
            this.loadFromAttribute(contentString);
            
            // Also update the attribute for consistency/reflection
            this.setAttribute('value', contentString);
        }        

        // --- Getters for attributes ---
        
        get storageKey() {
            return this.getAttribute('storage-key');
        }

        get autosaveEnabled() {
            return this.getAttribute('autosave') === 'true';
        }

        get autosaveInterval() {
            return parseInt(this.getAttribute('autosave-interval')) || 2000;
        }

        // --- Public API Methods ---

        /**
         * Save content to storage (memory only - no localStorage)
         */
        save() {
            if (!this.storageKey) {
                console.warn('No storage-key attribute set. Cannot save.');
                return null;
            }

            const delta = this.getDelta();
            
            // Store in memory on the window object
            if (!window._quillStorage) {
                window._quillStorage = {};
            }
            window._quillStorage[this.storageKey] = delta;
            
            console.log(`✅ Saved content for key: ${this.storageKey}`);
            
            this.dispatchEvent(new CustomEvent('content-saved', {
                detail: { key: this.storageKey, delta }
            }));
            
            return delta;
        }

        /**
         * Load content from storage (memory only)
         */
        load() {
            if (!this.storageKey) {
                console.warn('No storage-key attribute set. Cannot load.');
                return false;
            }

            if (!window._quillStorage || !window._quillStorage[this.storageKey]) {
                console.log(`ℹ️ No saved content found for key: ${this.storageKey}`);
                return false;
            }

            try {
                const delta = window._quillStorage[this.storageKey];
                this.setContents(delta);
                console.log(`📄 Loaded content for key: ${this.storageKey}`);
                
                this.dispatchEvent(new CustomEvent('content-loaded', {
                    detail: { key: this.storageKey, delta }
                }));
                
                return true;
            } catch (e) {
                console.error('Error loading saved content:', e);
                return false;
            }
        }

        /**
         * Load content from attribute (content or value)
         */
        loadFromAttribute(contentString) {
            try {
                // Try to parse as JSON (Delta format)
                const delta = JSON.parse(contentString);
                this.setContents(delta);
                console.log('📄 Loaded content from attribute');
                return true;
            } catch (e) {
                // If not valid JSON, treat as plain text
                console.warn('Content attribute is not valid JSON, treating as plain text');
                this.quill.setText(contentString);
                this.updateHiddenInput();
                return false;
            }
        }

        /**
         * Get content as Quill Delta format
         */
        getDelta() {
            return this.quill.getContents();
        }

        /**
         * Get content as HTML
         */
        getHTML() {
            return this.quill.root.innerHTML;
        }

        /**
         * Get plain text content
         */
        getText() {
            return this.quill.getText();
        }

        /**
         * Set content from Delta
         */
        setContents(delta) {
            this.quill.setContents(delta);
            this.updateHiddenInput();
        }

        /**
         * Set content from HTML
         */
        setHTML(html) {
            this.quill.root.innerHTML = html;
            this.updateHiddenInput();
        }

        /**
         * Insert text at current cursor position
         */
        insertText(text, format = {}) {
            const selection = this.quill.getSelection();
            const index = selection ? selection.index : this.quill.getLength();
            this.quill.insertText(index, text, format);
        }

        /**
         * Format current selection
         */
        format(name, value) {
            this.quill.format(name, value);
        }

        /**
         * Get current selection
         */
        getSelection() {
            return this.quill.getSelection();
        }

        /**
         * Clear all content
         */
        clear() {
            this.quill.setText('');
            this.updateHiddenInput();
        }

        /**
         * Toggle read-only mode
         */
        toggleReadOnly() {
            const isReadOnly = !this.quill.isEnabled();
            this.setReadOnly(isReadOnly);
        }

        /**
         * Set read-only mode
         */
        setReadOnly(readOnly) {
            this.quill.enable(!readOnly);
            
            if (readOnly) {
                this.setAttribute('readonly', '');
                //set border to non

            } else {
                this.removeAttribute('readonly');
            }

            this.dispatchEvent(new CustomEvent('readonly-change', {
                detail: { readOnly }
            }));
        }

        /**
         * Check if editor is read-only
         */
        isReadOnly() {
            return !this.quill.isEnabled();
        }

        /**
         * Focus the editor
         */
        focus() {
            this.quill.focus();
        }

        /**
         * Get Quill instance directly
         */
        getQuill() {
            return this.quill;
        }

        attributeChangedCallback(name, oldValue, newValue) {
            if (!this.quill) return;

            switch (name) {
                case 'readonly':
                    this.quill.enable(newValue === null);
                    break;
                case 'placeholder':
                    this.quill.root.dataset.placeholder = newValue;
                    break;
                case 'content':
                case 'value':
                    if (newValue && newValue !== oldValue) {
                        this.loadFromAttribute(newValue);
                    }
                    break;
            }
        }
    }

    // Register the custom element
    customElements.define('quill-editor', QuillEditor);
    
    console.log('✅ QuillEditor custom element registered');
})();
</script>
        <?php
    }
    
    /**
     * Helper function to create a basic editor instance
     * 
     * @param string $id Editor ID
     * @param array $attributes Additional attributes (placeholder, height, etc.)
     * @return string HTML for the editor element
     */
    public static function create($id, $attributes = []) {
        $attrs = ['id' => $id];
        $attrs = array_merge($attrs, $attributes);
        
        $attrString = '';
        foreach ($attrs as $key => $value) {
            $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($value) . '"';
        }
        
        return '<quill-editor' . $attrString . '></quill-editor>';
    }
    
    /**
     * Output an editor element directly
     * 
     * @param string $id Editor ID
     * @param array $attributes Additional attributes
     */
    public static function render($id, $attributes = []) {
        echo self::create($id, $attributes);
    }
}

QuillEditor::init();
?>

<style>
  .quill-wrapper.readonly .ql-toolbar {
    display: none !important;
  }
  .quill-wrapper.readonly .ql-container {
      border-top: 1px solid #ccc;
  }
</style>
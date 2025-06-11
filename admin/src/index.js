/**
 * Entry point for the SchemaCraft metabox React application.
 *
 * This version is refactored to use modern React Hooks (useState, useEffect)
 * and WordPress data hooks (useSelect, useDispatch) for better performance and maintainability.
 */
import './style.scss';

import { render, Fragment, useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, DropdownMenu, MenuGroup, MenuItem, TextControl, Spinner } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';

// --- Main App Component ---
const App = () => {
    // WordPress Data Store Integration using Hooks
    const { postMeta, isSaving, isNewPost } = useSelect((select) => {
        const editorSelect = select('core/editor');
        if (!editorSelect) {
            return { postMeta: undefined, isSaving: false, isNewPost: true };
        }
        return {
            postMeta: editorSelect.getEditedPostAttribute('meta'),
            isSaving: editorSelect.isSavingPost(),
            isNewPost: editorSelect.isEditedPostNew(),
        };
    }, []);

    const { editPost } = useDispatch('core/editor');

    // Component State using Hooks
    const [selectedSchemaType, setSelectedSchemaType] = useState('');
    const [courseName, setCourseName] = useState('');
    const [courseProvider, setCourseProvider] = useState('');
    const [initialLoadDone, setInitialLoadDone] = useState(false);

    // This effect runs once on mount to initialize the state from post meta
    useEffect(() => {
        if (postMeta && !initialLoadDone) {
            setSelectedSchemaType(postMeta._schemacraft_selected_schema_type || '');
            setCourseName(postMeta._schemacraft_course_name || '');
            setCourseProvider(postMeta._schemacraft_course_provider || '');
            setInitialLoadDone(true);
        } else if (!postMeta) {
             // Handles cases where postMeta is not yet available
            setInitialLoadDone(true);
        }
    }, [postMeta, initialLoadDone]);


    // Available schemas (could come from PHP in the future)
    const availableSchemas = SchemaCraftData.availableSchemas || [
        { value: 'course', label: __('Course', 'schemacraft') },
        { value: 'article', label: __('Article', 'schemacraft') },
    ];

    // --- Event Handlers ---

    const handleSchemaSelect = (schemaValue) => {
        setSelectedSchemaType(schemaValue);
        // Save the new schema type to the database
        editPost({
            meta: {
                _schemacraft_selected_schema_type: schemaValue,
            },
        });
    };

    const handleFieldChange = (metaKey, value) => {
        // Update the local state first for a responsive UI
        if (metaKey === '_schemacraft_course_name') setCourseName(value);
        if (metaKey === '_schemacraft_course_provider') setCourseProvider(value);

        // Save the change to the database
        editPost({
            meta: {
                [metaKey]: value,
            },
        });
    };

    const handleClearSchema = () => {
        // Clear local state
        setSelectedSchemaType('');
        setCourseName('');
        setCourseProvider('');

        // Clear meta fields in the database
        editPost({
            meta: {
                _schemacraft_selected_schema_type: '',
                _schemacraft_course_name: '',
                _schemacraft_course_provider: '',
            },
        });
    };


    // --- Rendering Logic ---

    const renderSchemaFields = () => {
        if (!selectedSchemaType) return null;

        switch (selectedSchemaType) {
            case 'course':
                return (
                    <Fragment>
                        <TextControl
                            label={__('Course Name', 'schemacraft')}
                            value={courseName}
                            onChange={(value) => handleFieldChange('_schemacraft_course_name', value)}
                            help={__('Enter the name of the course.', 'schemacraft')}
                        />
                        <TextControl
                            label={__('Provider', 'schemacraft')}
                            value={courseProvider}
                            onChange={(value) => handleFieldChange('_schemacraft_course_provider', value)}
                            help={__('Enter the organization or person providing the course.', 'schemacraft')}
                        />
                    </Fragment>
                );
            case 'article':
                return <p>{__('Article fields will be shown here.', 'schemacraft')}</p>;
            default:
                return <p>{__('Selected schema type has no defined fields yet.', 'schemacraft')}</p>;
        }
    };

    // --- Main Component Return ---

    if (!initialLoadDone && !isNewPost) {
        return (
            <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '100px' }}>
                <Spinner />
                <p style={{ marginLeft: '8px' }}>{__('Loading schema data...', 'schemacraft')}</p>
            </div>
        );
    }
    
    const selectedSchemaLabel = selectedSchemaType
        ? (availableSchemas.find(s => s.value === selectedSchemaType)?.label || selectedSchemaType)
        : '';

    return (
        <Fragment>
            <h2>{__('SchemaCraft Options', 'schemacraft')}</h2>

            {!selectedSchemaType && (
                <DropdownMenu
                    icon={null}
                    label={__('Select Schema Type', 'schemacraft')}
                    toggleProps={{
                        children: __('Add Schema Type', 'schemacraft'),
                        variant: 'primary',
                    }}
                >
                    {({ onClose }) => (
                        <MenuGroup label={__('Available Schema Types', 'schemacraft')}>
                            {availableSchemas.map((schema) => (
                                <MenuItem
                                    key={schema.value}
                                    onClick={() => {
                                        handleSchemaSelect(schema.value);
                                        onClose();
                                    }}
                                >
                                    {schema.label}
                                </MenuItem>
                            ))}
                        </MenuGroup>
                    )}
                </DropdownMenu>
            )}

            {selectedSchemaType && (
                <div style={{ marginTop: '15px' }}>
                    <h3>
                        {__('Schema:', 'schemacraft')} <strong>{selectedSchemaLabel}</strong>
                        <Button
                            variant="link"
                            style={{ marginLeft: '10px', color: '#cc0000', textDecoration: 'underline' }}
                            onClick={handleClearSchema}
                        >
                            {__('Change / Clear', 'schemacraft')}
                        </Button>
                    </h3>
                    {renderSchemaFields()}
                </div>
            )}

            {isSaving && <p><em>{__('Saving...', 'schemacraft')}</em></p>}
        </Fragment>
    );
};

// --- Initial Render ---
document.addEventListener('DOMContentLoaded', function () {
    const rootElement = document.getElementById('schemacraft-metabox-react-root');
    if (rootElement) {
        render(<App />, rootElement);
    } else {
        console.error('SchemaCraft: Root element #schemacraft-metabox-react-root not found.');
    }
});

/**
 * Entry point for the SchemaCraft metabox React application.
 */
import './style.scss';

import { render, Component, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, DropdownMenu, MenuGroup, MenuItem, TextControl, Spinner } from '@wordpress/components';

class App extends Component {
    constructor(props) {
        super(props);
        this.state = {
            selectedSchemaType: '',
            courseName: '',
            courseProvider: '',
            initialLoadDone: false,
            isDropdownOpen: false,
        };
        // console.log('SchemaCraftData from PHP:', SchemaCraftData);

        this.availableSchemas = SchemaCraftData.availableSchemas || [
            { value: 'course', label: __('Course', 'schemacraft') },
            { value: 'article', label: __('Article', 'schemacraft') },
        ];
    }

    componentDidMount() {
        this.loadSchemaData();
    }

    loadSchemaData = () => {
        if ( !window.wp || !wp.data || !wp.data.select || !wp.data.select('core/editor') ) {
            console.warn('SchemaCraft: wp.data.select(\'core/editor\') not available for loading meta.');
            this.setState({ initialLoadDone: true });
            return;
        }

        const meta = wp.data.select('core/editor').getEditedPostAttribute('meta');

        if (typeof meta !== 'undefined') {
            this.setState({
                selectedSchemaType: meta._schemacraft_selected_schema_type || '',
                courseName: meta._schemacraft_course_name || '',
                courseProvider: meta._schemacraft_course_provider || '',
                initialLoadDone: true,
            });
        } else {
            // This case might occur if the editor is not fully initialized or if it's a new post without any meta yet.
            // console.warn('SchemaCraft: Post meta was undefined on load (this may be normal for new posts).');
            this.setState({ initialLoadDone: true });
        }
    };

    toggleDropdown = () => {
        this.setState((prevState) => ({ isDropdownOpen: !prevState.isDropdownOpen }));
    };

    handleSchemaSelect = (schemaValue) => {
        // Preserve existing data if re-selecting the same type or for specific fields
        const currentCourseName = schemaValue === 'course' ? this.state.courseName : '';
        const currentCourseProvider = schemaValue === 'course' ? this.state.courseProvider : '';

        this.setState({
            selectedSchemaType: schemaValue,
            isDropdownOpen: false,
            courseName: currentCourseName,
            courseProvider: currentCourseProvider,
            // Reset other schema type fields here if they exist
        });
        // Staging changes to Gutenberg will be handled in the next step
    };

    handleFieldChange = (fieldName, value) => {
        this.setState({ [fieldName]: value });
        // Staging changes to Gutenberg will be handled in the next step
    };

    renderSchemaFields = () => {
        const { selectedSchemaType, courseName, courseProvider } = this.state;
        if (!selectedSchemaType) return null;

        switch (selectedSchemaType) {
            case 'course':
                return (
                    <Fragment>
                        <TextControl
                            label={__('Course Name', 'schemacraft')}
                            value={courseName}
                            onChange={(value) => this.handleFieldChange('courseName', value)}
                            help={__('Enter the name of the course.', 'schemacraft')}
                        />
                        <TextControl
                            label={__('Provider', 'schemacraft')}
                            value={courseProvider}
                            onChange={(value) => this.handleFieldChange('courseProvider', value)}
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

    render() {
        const { selectedSchemaType, initialLoadDone, isDropdownOpen } = this.state;

        // Show loading spinner only for existing posts during initial data fetch
        if (!initialLoadDone && SchemaCraftData.postId) {
            return (
                <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', minHeight: '100px' }}>
                    <Spinner />
                    <p style={{marginLeft: '8px'}}>{__('Loading schema data...', 'schemacraft')}</p>
                </div>
            );
        }

        const selectedSchemaLabel = selectedSchemaType
            ? (this.availableSchemas.find(s => s.value === selectedSchemaType)?.label || selectedSchemaType)
            : '';

        return (
            <Fragment>
                <h2>{__('SchemaCraft Options', 'schemacraft')}</h2>

                {!selectedSchemaType && ( // Show "Add" button only if no schema type is selected
                    <Button variant="primary" onClick={this.toggleDropdown} aria-expanded={isDropdownOpen}>
                        {__('Add Schema Type', 'schemacraft')}
                    </Button>
                )}

                {isDropdownOpen && !selectedSchemaType && ( // Show dropdown only if open and no type selected
                     <DropdownMenu
                        label={__('Select Schema Type', 'schemacraft')}
                        onClose={ () => this.setState({ isDropdownOpen: false }) }
                    >
                        { ( { onClose } ) => (
                            <MenuGroup label={__('Available Schema Types', 'schemacraft')}>
                                {this.availableSchemas.map((schema) => (
                                    <MenuItem
                                        key={schema.value}
                                        onClick={() => { this.handleSchemaSelect(schema.value); onClose(); }}
                                    >
                                        {schema.label}
                                    </MenuItem>
                                ))}
                            </MenuGroup>
                        ) }
                    </DropdownMenu>
                )}

                {selectedSchemaType && ( // Show fields and schema type info if a type is selected
                    <div style={{marginTop: '15px'}}>
                        <h3>
                            {__('Schema:', 'schemacraft')} <strong>{selectedSchemaLabel}</strong>
                            {/* Add a button/link here to change/clear the schema type */}
                            <Button
                                variant="link"
                                style={{ marginLeft: '10px', textDecoration: 'underline' }}
                                onClick={() => this.setState({ selectedSchemaType: '', courseName: '', courseProvider: '' /* reset all fields */ })}
                            >
                                {__('Change / Clear', 'schemacraft')}
                            </Button>
                        </h3>
                        {this.renderSchemaFields()}
                    </div>
                )}
            </Fragment>
        );
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const rootElement = document.getElementById('schemacraft-metabox-react-root');
    if (rootElement) {
        render(<App />, rootElement);
    } else {
        console.error('SchemaCraft: Root element #schemacraft-metabox-react-root not found.');
    }
});

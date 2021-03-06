import React, {PureComponent, RefObject} from "react";
import Modal from "../Layout/Modal";
import Button from "../ui/Button";
import {IPermissions} from "../../types";
import AclForm from "../Acl/AclForm";
import {FormikHelpers, FormikProps} from "formik";

export type AbstractEditProps = {
    id: string,
    onClose: () => void;
}

type State<T extends IPermissions> = {
    loading: boolean;
    saving: boolean;
    data: T | undefined;
};

export default abstract class AbstractEdit<T extends IPermissions, FP> extends PureComponent<AbstractEditProps, State<T>> {
    protected readonly formRef: RefObject<FormikProps<FP>>;

    state = {
        saving: false,
        loading: true,
        data: undefined,
    };

    constructor(props: AbstractEditProps) {
        super(props);

        this.formRef = React.createRef();
    }

    componentDidMount() {
        this.load();
    }

    async load() {
        const data = await this.loadItem();

        this.setState({
            data,
            loading: false,
        });
    }

    abstract loadItem(): Promise<T>;

    abstract handleSave(data: FP): Promise<boolean>;

    abstract getType(): string;
    abstract getTitle(): string | null;

    getData(): T | null {
        return this.state.data || null;
    }

    renderModalHeader() {
        return <h4>Edit {this.getTitle()}</h4>
    }

    save = (): void => {
        this.formRef.current!.submitForm();
    }

    protected async onSubmit(data: FP, actions: FormikHelpers<FP>) {
        this.setState({saving: true}, async (): Promise<void> => {
            const res = await this.handleSave(data);
            if (!res) {
                this.setState({saving: false});
                actions.setSubmitting(false);
                return;
            }

            this.props.onClose();
        });
    }

    render() {
        const {saving} = this.state;

        return <Modal
            loading={saving}
            onClose={this.props.onClose}
            header={this.renderModalHeader.bind(this)}
            footer={({onClose}) => <>
                <Button
                    onClick={onClose}
                    className={'btn-secondary'}
                    disabled={saving}
                >
                    Close
                </Button>
                <Button
                    onClick={this.save}
                    className={'btn-primary'}
                    disabled={saving}
                >
                    Save changes
                </Button>
            </>}
        >
            {this.renderContent()}
        </Modal>
    }

    renderContent() {
        if (this.state.loading) {
            return 'Loading...';
        }

        if (!this.state.data) {
            return 'Not found!';
        }
        const data: T = this.state.data!;

        return <div>
            {this.renderForm()}
            <hr/>
            {data.capabilities.canEditPermissions ? <div>
                <h4>Permissions</h4>
                <AclForm
                    objectId={this.props.id}
                    objectType={this.getType()}
                />
            </div> : ''}
        </div>;
    }

    abstract renderForm(): React.ReactNode;
}

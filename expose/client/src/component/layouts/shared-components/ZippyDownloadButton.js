import React, {PureComponent} from 'react';
import {PropTypes} from 'prop-types';
import config from '../../../lib/config';
import apiClient from "../../../lib/apiClient";
import {dataShape} from "../../props/dataShape";
import {renderDownloadTermsModal, renderDownloadViaEmail} from "./DownloadViaEmailProxy";
import {isTermsAccepted} from "../../../lib/credential";

export default class ZippyDownloadButton extends PureComponent {
    static propTypes = {
        id: PropTypes.string.isRequired,
        data: dataShape.isRequired,
    };

    state = {
        disabled: false,
    };

    onDownload = async () => {
        const {data} = this.props;
        if (!data.downloadTerms.enabled || isTermsAccepted('pd_' + data.id)) {
            if (true === data.downloadViaEmail) {
                this.setState({
                    displayDownloadViaEmail: true,
                    pendingDownloadUrl: `${config.getApiBaseUrl()}/publications/${this.props.id}/zippy/download-request`
                });

                return;
            }

            this.disableButtonForDownload();
            const res = await apiClient.get(`${config.getApiBaseUrl()}/publications/${this.props.id}/download-via-zippy`);
            window.open(res.downloadUrl, '_blank');
        }

        this.setState({
            displayDownloadTerms: true,
        });
    }

    disableButtonForDownload() {
        this.setState(prevState => {
            if (prevState.disabled) {
                return;
            }

            return {disabled: true};
        }, () => {
            setTimeout(() => {
                this.setState({disabled: false});
            }, 5000);
        });
    }

    render() {
        return <>
            {renderDownloadTermsModal.call(this)}
            {renderDownloadViaEmail.call(this)}
            <button
                disabled={this.state.disabled}
                className={'btn btn-secondary'}
                type={'button'}
                title={'Download'}
                onClick={this.onDownload}
            >
                <span role={'img'}>🗜</span>️ Download archive
            </button>
        </>
    }
}


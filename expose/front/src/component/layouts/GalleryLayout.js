import React from 'react';
import ImageGallery from 'react-image-gallery';
import {dataShape} from "../props/dataShape";
import {PropTypes} from 'prop-types';
import Description from "./shared-components/Description";
import {defaultMapProps, initMapbox} from "./mapbox/MapboxLayout";
import mapboxgl from 'mapbox-gl';

class GalleryLayout extends React.Component {
    static propTypes = {
        data: dataShape,
        assetId: PropTypes.string,
        options: PropTypes.object,
        mapOptions: PropTypes.object,
    };

    state = {
        showFullscreenButton: true,
        showPlayButton: true,
        showVideo: {},
        currentIndex: null,
    };

    map;

    constructor(props) {
        super(props);

        this.mapContainer = React.createRef();
        this.sliderRef = React.createRef();
    }

    componentDidMount() {
        if (this.props.options.displayMap) {
            this.initMap();
        }
    }

    initMap() {
        if (!this.mapContainer.current) {
            return;
        }

        const {data, options, mapOptions} = this.props;

        let locationAsset = data.assets.filter(a => a.asset.lat)[0];
        locationAsset = locationAsset ? locationAsset.asset : mapOptions;

        switch (options.mapProvider) {
            default:
            case 'mapbox':
                this.map = initMapbox(this.mapContainer.current, {
                    ...defaultMapProps,
                    ...(locationAsset ? {
                        lat: locationAsset.lat,
                        lng: locationAsset.lng,
                    } : {}),
                    zoom: locationAsset && locationAsset.zoom ? locationAsset.zoom : 5
                });
                data.assets.forEach((a, pos) => {
                    const {asset} = a;
                    if (!(asset.lat && asset.lng)) {
                        return;
                    }
                    const marker = new mapboxgl.Marker()
                        .setLngLat([
                            asset.lng,
                            asset.lat,])
                        .addTo(this.map)
                    ;

                    marker.getElement().addEventListener('click', () => {
                        this.goto(pos);
                    });
                });
                break;
        }
    }

    goto(index) {
        if (!this.sliderRef.current) {
            return;
        }
        this.sliderRef.current.slideToIndex(index);
    }

    onSlide = (offset) => {
        this.setState({currentIndex: offset});
        this.resetVideo();

        if (this.map) {
            const asset = this.props.data.assets[offset].asset;
            if (asset.lat && asset.lng) {
                this.map.flyTo({
                    center: [
                        asset.lng,
                        asset.lat,
                    ],
                    essential: true
                });
            }
        }
    };

    resetVideo() {
        this.setState({
            showVideo: {},
            showFullscreenButton: true,
            showPlayButton: true,
        });
    }

    toggleShowVideo(url) {
        this.setState(prevState => {
            const showVideo = {...prevState.showVideo};
            const wasShown = !!showVideo[url];
            showVideo[url] = !wasShown;

            return {
                showVideo,
                showPlayButton: wasShown,
                showFullscreenButton: wasShown,
            }
        });
    }

    renderVideo = (item) => {
        const {showVideo} = this.state;

        return <div className='image-gallery-image'>
            {
                showVideo[item.url] ?
                    <div className='video-wrapper'>
                        <video controls autoPlay={true}>
                            <source src={item.url} type={'video/mp4'}/>
                            Sorry, your browser doesn't support embedded videos.
                        </video>
                    </div>
                    : <div onClick={this.toggleShowVideo.bind(this, item.url)}>
                        <div className='play-button'/>
                        <img src={item.thumbUrl} alt={item.title}/>
                        {
                            item.description &&
                            <span
                                className='image-gallery-description'
                                style={{right: '0', left: 'initial'}}
                            >
                            {item.description}
                          </span>
                        }
                    </div>
            }
        </div>;
    };

    render() {
        const {assetId, data, options} = this.props;
        const {currentIndex} = this.state;
        const {
            title,
            assets,
        } = data;

        const {
            showFullscreenButton,
            showPlayButton,
        } = this.state;

        let startIndex = 0;
        if (currentIndex) {
            startIndex = currentIndex;
        } else if (assetId) {
            startIndex = assets.findIndex(a => a.id === assetId);
            if (startIndex < 0) {
                startIndex = assets.findIndex(a => a.slug === assetId);
                if (startIndex < 0) {
                    startIndex = 0;
                }
            }
        }

        return <div className={`layout-gallery`}>
            <div className="container">
                <h1>{title}</h1>
                <Description
                    descriptionHtml={data.description}
                />
                {assets.length > 0 ?
                    <ImageGallery
                        ref={this.sliderRef}
                        startIndex={startIndex}
                        onSlide={this.onSlide}
                        showFullscreenButton={showFullscreenButton}
                        showPlayButton={showPlayButton}
                        items={assets.map(a => ({
                            original: a.asset.url,
                            thumbnail: a.asset.thumbUrl,
                            description: 'toto',
                            asset: a.asset,
                            renderItem: this.renderItem,
                        }))}
                    /> : 'Gallery is empty'}
                {options.displayMap ? this.renderMap() : ''}
            </div>
        </div>
    }

    renderMap() {
        return <div className={'gallery-map'}>
            <div
                className={'map-container'}
                ref={this.mapContainer}
            />
        </div>
    }

    renderItem = ({asset}) => {
        if (-1 === asset.mimeType.indexOf('image/')) {
            return this.renderVideo(asset);
        }

        return <div className="image-gallery-image">
            <img
                alt={asset.title || 'Image'}
                src={asset.url}/>
            {asset.description ? <span
                className="image-gallery-description">
                    <Description descriptionHtml={asset.description}/>
                </span> : ''}
        </div>;
    }
}

export default GalleryLayout;

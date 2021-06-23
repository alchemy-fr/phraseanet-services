import React, {useState, useRef} from 'react';
import {usePdf} from '@mikecousins/react-pdf';

const PDFViewer = (props) => {
    const [page, setPage] = useState(1);
    const canvasRef = useRef(null);

    const {pdfDocument} = usePdf({
        file: props.file,
        page,
        scale: .8,
        canvasRef,
    });

    return <div className={'pdf-viewer'}>
        {!pdfDocument && <span>Loading...</span>}
        <canvas
            ref={canvasRef}
        />
        {pdfDocument && <div className="pdf-controls">
            <button
                type={'button'}
                disabled={page <= 1}
                onClick={() => setPage(page - 1)}
                className="btn btn-secondary"
            >
                &lt;
            </button>
            {' '}
            <button
                type={'button'}
                disabled={page === pdfDocument.numPages}
                onClick={() => setPage(page + 1)}
                className="btn btn-secondary">
                &gt;
            </button>
        </div>}
    </div>
};

export default PDFViewer;

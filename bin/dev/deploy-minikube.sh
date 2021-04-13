#!/bin/bash

set -e

BASEDIR="$(dirname $0)"
DIR="${BASEDIR}/../.."

NS=${NS:-"ps"}
RELEASE_NAME="ps"
CHART_DIR="${DIR}/infra/helm/ps"
VALUE_SRC="${DIR}/infra/helm/sample.yaml"

kubectl config use-context minikube

case $1 in
  uninstall)
    helm uninstall ${RELEASE_NAME} || true;
    ;;
  validate)
    helm install --dry-run --debug ${RELEASE_NAME} "${CHART_DIR}" \
        -f "${VALUE_SRC}" \
        --namespace $NS
    ;;
  update)
    echo "Updating..."
    helm upgrade ${RELEASE_NAME} "${CHART_DIR}" \
        -f "${VALUE_SRC}" \
        --namespace $NS
    ;;

  *)
    if [ ! -d "${CHART_DIR}/charts" ]; then
      (cd "${CHART_DIR}" && helm dependency update)
    fi
    kubectl create ns $NS || true
    helm uninstall ${RELEASE_NAME} --namespace $NS || true;
    kubectl -n $NS delete pvc elasticsearch-master-elasticsearch-master-0 || true
    while [ "$(kubectl -n $NS get pvc | wc -l)" != 0 ] || [ "$(kubectl -n $NS get pods | wc -l)" != 0 ]
    do
      echo "Waiting for resources to be deleted..."
      sleep 5
    done
    echo "Installing release ${RELEASE_NAME} in namespace $NS..."
    helm install ${RELEASE_NAME} "${CHART_DIR}" \
        -f "${VALUE_SRC}" \
        --namespace $NS
    ;;
esac

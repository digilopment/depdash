function dataSource() {
    const scriptElement = document.currentScript;
    const dataSource = scriptElement.getAttribute('data-source');
    if (dataSource) {
        return dataSource;
    }
    return '/json/data.json';
}
function depDashMainApp(){
    fetch(dataSource()).then(response => response.json()).then(data => {
    const template = `
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <h1 style="padding-bottom:30px; padding-top: 20px;">Deployment Dashboard</h1>
                        ${data.map(item => `
                          <h2>${item.deploymentProject.name}</h2>
                          <table class="table">
                              <thead>
                                  <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Name</th>
                                      <th scope="col">Branch or tag</th>
                                      <th scope="col">Status</th>
                                      <th scope="col">Finished at</th>
                                      <th scope="col">Triggered by</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  ${item.environmentStatuses.map(env => `
                                    ${(() => {
                                        let textColor;
                                        if (env.deploymentResult.deploymentState === 'SUCCESS') {
                                            textColor = "green";
                                        } else if (env.deploymentResult.deploymentState === 'UNKNOWN') {
                                            textColor = "orange";
                                        } else if (env.deploymentResult.deploymentState === 'FAILED') {
                                            textColor = "red";
                                        }
                                        return `
                                        <tr>
                                            <th scope="row"><a target="_blank" href="${env.environment.repoUrl}">${env.environment.id}</a></th>
                                            <td>${env.environment.name}</td>
                                            <td>${env.deploymentResult.deploymentVersionName}</td>
                                            <td style="color: ${textColor}">
                                              ${env.deploymentResult.deploymentState === 'UNKNOWN' ? 'IN PROGRESS' : env.deploymentResult.deploymentState}
                                            </td>
                                            <td>${env.deploymentResult.finishedDate ? moment(env.deploymentResult.finishedDate).fromNow() : ''}</td>
                                            <td>
                                                ${env.deploymentResult.reasonSummary}
                                            </td>
                                        </tr>`;
                                    })()}
                                  `).join('')}
                              </tbody>
                          </table>
                          <br/>`
                        ).join('')}
                </div>
            </div>
        </div>`;
        const element = document.getElementById("mkz-prod-www1");
        element.innerHTML = template;
    });
}

depDashMainApp();

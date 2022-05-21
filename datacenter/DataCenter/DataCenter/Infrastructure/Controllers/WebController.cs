using DataCenter.Common.Network.HttpWeb;
using DataCenter.Common.Network.HttpWeb.Attributes;
using DataCenter.Data.Dtos;

using DataCenter.Infrastructure.Services;
using DataCenter.Infrastructure.Services.Interfaces;

using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Controllers
{
    [WebRoute("web")]
    public class WebController
    {
        private readonly IWebService webService;

        public WebController(WebService webService)
        {
            this.webService = webService;
        }

        public async Task<UserWebProfileDto> GetUserProfile(string userName, RequestContext requestContext)
        {
            string unitId = requestContext.UnitId;
            return await webService.GetUserProfile(unitId, userName);
        }

        public async Task<string> GetPassword(string userName)
        {
            return await webService.GetPassword(userName);
        }
    }
}

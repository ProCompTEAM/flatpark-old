using DataCenter.Common.Network.HttpWeb;
using DataCenter.Common.Network.HttpWeb.Attributes;

using DataCenter.Data.Dtos;

using DataCenter.Infrastructure.Services;
using DataCenter.Infrastructure.Services.Interfaces;

using System.Collections.Generic;
using System.Threading.Tasks;

namespace DataCenter.Infrastructure.Controllers
{
    [WebRoute("floating-texts")]
    public class FloatingTextsController
    {
        private readonly IFloatingTextsService floatingTextsService;

        public FloatingTextsController(FloatingTextsService floatingTextsService)
        {
            this.floatingTextsService = floatingTextsService;
        }

        public List<FloatingTextDto> GetAll(RequestContext context)
        {
            string unitId = context.UnitId;
            return floatingTextsService.GetAll(unitId);
        }

        public async Task<FloatingTextDto> Save(LocalFloatingTextDto floatingTextData, RequestContext context)
        {
            string unitId = context.UnitId;
            return await floatingTextsService.Save(unitId, floatingTextData);
        }

        public async Task<bool> Remove(PositionDto position, RequestContext context)
        {
            string unitId = context.UnitId;
            return await floatingTextsService.Remove(unitId, position.World, position.X, position.Y, position.Z);
        }
    }
}